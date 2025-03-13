@extends('layouts.app')

@section('title_full', 'Remote Response - ' . $mailbox->name)

@section('body_attrs')@parent data-mailbox_id="{{ $mailbox->id }}"@endsection

@section('sidebar')
    @include('partials/sidebar_menu_toggle')
    @include('mailboxes/sidebar_menu')
@endsection

@section('content')
    <div class="section-heading">
        Remote Response
    </div>
    <div class="col-xs-12">
        <form class="form-horizontal margin-top margin-bottom" method="POST" action="">
            {{ csrf_field() }}

            <div class="form-group">
                <label for="rr_enabled" class="col-sm-2 control-label">{{ __("Enable module") }}</label>

                <div class="col-sm-6">
                    <div class="controls">
                        <div class="onoffswitch-wrap">
                            <div class="onoffswitch">
                                <input type="checkbox" name="rr_enabled" id="rr_enabled" class="onoffswitch-checkbox"
                                    {!! $settings['enabled'] ? "checked" : "" !!}
                                >
                                <label class="onoffswitch-label" for="rr_enabled"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">{{ __("Remote URL") }}</label>

                <div class="col-sm-6">
                    <input type="url" name="url" class="form-control" placeholder="https://remote-reponse-server.com/some-endpoint" value="{{ $settings['url'] }}" required />
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">{{ __("Timeout") }}</label>

                <div class="col-sm-6">
                    <input type="number" name="timeout" class="form-control" value="{{ $settings['timeout'] }}" required />
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">{{ __("Method") }}</label>

                <div class="col-sm-6">
                    <select id="method" class="form-control input-sized" name="method" required data-saved-method="{{ old('method', $settings['method'] ?? '') }}">
                        <option value="POST">POST</option>
                        <option value="GET">GET</option>
                   </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">{{ __("JSON encoded headers (Optional)") }}</label>

                <div class="col-sm-6">
                    <textarea rows="15" name="headers" class="form-control" placeholder="{Authorization: Bearer Abdhj.......}">{{ $settings['headers'] }}</textarea>
                </div>
            </div>            

            <meta name="csrf-token" content="{{ csrf_token() }}">            

            <div class="form-group margin-top margin-bottom">
                <div class="col-sm-6 col-sm-offset-2">
                    <button type="submit" class="btn btn-primary">
                        {{ __("Save") }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('body_bottom')
    @parent

@endsection
