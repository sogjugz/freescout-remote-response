<?php

namespace Modules\SsRemoteResponse\Http\Controllers;

use App\Conversation;
use App\Mailbox;
use App\Thread;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\SsRemoteResponse\Entities\RemoteResponseSettings;

class SsRemoteResponseController extends Controller
{
    public function generate(Request $request)
    {
        if (Auth::user() === null) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $settings = RemoteResponseSettings::query()->findOrFail($request->input('mailbox_id'));

        if (!$settings->enabled) {
            return response()->json(['error' => 'Remote response is not enabled'], 400);
        }

        $threads = Conversation::find($request->input('conversation_id'))
            ->threads()
            ->where('state', Thread::STATE_PUBLISHED)
            ->orderBy('created_at')
            ->get()
            ->map(fn ($t) => ['from' => $t->source_via === 1 ? 'Customer' : 'Agent', 'content' =>  $this->htmlToMarkdown($t->body)]);

        $conversation = '';

        foreach($threads as $thread) {
           $conversation .= $thread['from'] . ': ' .PHP_EOL . $thread['content'] . PHP_EOL . '---' . PHP_EOL;
        }

        $client = new \GuzzleHttp\Client();

        try {

            if ($settings->method === 'POST') {
                $response = $client->post($settings->url, [
                    'timeout' => $settings->timeout,
                    'headers' => empty($settings->headers) ? [] : json_decode($settings->headers, true),
                    'json' => [
                        'conversation_content' => $conversation,
                        'customer_name' => $request->input('customer_name', 'No customer name provided'),
                        'customer_email' => $request->input('customer_email', 'No customer email provided'),
                        'conversation_subject' => $request->input('conversation_subject', 'No conversation subject provided'),
                    ]
                ]); 
            } else {
                $response = $client->get($settings->url, [
                    'timeout' => $settings->timeout,
                    'headers' => empty($settings->headers) ? [] : json_decode($settings->headers, true),
                    'query' => [
                        'conversation_content' => $conversation,
                        'customer_name' => $request->input('customer_name', 'No customer name provided'),
                        'customer_email' => $request->input('customer_email', 'No customer email provided'),
                        'conversation_subject' => $request->input('conversation_subject', 'No conversation subject provided'),
                    ]
                ]);
            }

            $userResponse = (string) $response->getBody();
        } catch (\Exception $e) {
            $userResponse = 'Failed obtaining response: ' . $e->getMessage();
        }

        return response()->json([
            'answer' => $userResponse
        ], 200);
    }

    public function settings($mailbox_id)
    {
        $mailbox = Mailbox::query()->findOrFail($mailbox_id);
        $settings = RemoteResponseSettings::query()->find($mailbox_id);

        if (empty($settings)) {
            $settings['mailbox_id'] = $mailbox_id;
            $settings['enabled'] = false;
            $settings['url'] = '';
            $settings['timeout'] = 30;
            $settings['method'] = 'POST';
            $settings['headers'] = null;
        }

        return view('ssremoteresponse::settings', [
            'mailbox' => $mailbox,
            'settings' => $settings
        ]);
    }

    public function saveSettings($mailbox_id, Request $request)
    {
        if (json_encode($request->input('headers')) === 'null') {
            $request->merge(['headers' => null]);
        }

        RemoteResponseSettings::query()->updateOrCreate(
            ['mailbox_id' => $mailbox_id],
            [
                'enabled' => $request->input('rr_enabled', false) === 'on',
                'url' => $request->input('url', ''),
                'timeout' => $request->input('timeout', 30),
                'method' => $request->input('method', 'POST'),
                'headers' => $request->input('headers', null)
            ]
        );

        \Session::flash('flash_success_floating', __('Settings updated'));

        return redirect()->route('ss-remote-response.settings', ['mailbox_id' => $mailbox_id]);
    }

    public function checkIsEnabled(Request $request)
    {
        $settings = RemoteResponseSettings::query()->find($request->input('mailbox'));

        if (empty($settings)) {
            return response()->json(['enabled' => false], 200);
        }

        return response()->json(['enabled' => $settings->enabled], 200);
    }

    private function htmlToMarkdown($html)
    {
        try {
            // Convertir enlaces <a href="url">texto</a> a [texto](url)
            $html = preg_replace_callback(
                '/<a\s+href="([^"]+)">([^<]+)<\/a>/i',
                function($matches) {
                    return '[' . $matches[2] . '](' . $matches[1] . ')';
                },
                $html
            );
    
            // Convertir saltos de línea <br> a nueva línea
            $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
    
            // Convertir párrafos <p> en nueva línea seguida de contenido
            $html = preg_replace('/<p>(.*?)<\/p>/i', "\n$1\n", $html);
    
            // Limpiar etiquetas HTML restantes (opcional)
            $html = strip_tags($html);
    
            // Eliminar espacios en blanco adicionales
            return trim($html);
        } catch (Exception $e) {
            return strip_tags($html);
        }
    }
}
