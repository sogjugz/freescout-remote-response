function remoteResponseInit() {
    $(document).ready(function () {
        // Add event listeners
        $(document).on("click", ".rrbutton", injectAnswer);

        if (document.location.pathname.startsWith("/conversation")) {
            const mailbox_id = $("body").attr("data-mailbox_id");
            $.ajax({
                url: "/ss-remote-response/is_enabled?mailbox=" + mailbox_id,
                dataType: "json",
                success: function (response, status) {
                    if (!response.enabled) {
                        $(".rrbutton").remove();
                    }
                },
            });

            // Add button to reply form
            $(".conv-reply-body .note-toolbar > .note-btn-group:first").append(
                '<button type="button" class="note-btn btn btn-default btn-sm rrbutton" tabindex="-1" title aria-label="Process remote response" data-original-title="Process remote response">' +
                    '<i id="bt-send-remote-response" class="glyphicon glyphicon-cloud-upload"></i>' +
                    '<i id="bt-sending-remote-response" class="glyphicon glyphicon-refresh hidden"></i>' +
                    "</button>"
            );
        }
    });
}

function getHTMLFromAnswer(response) {
    const answer = response.answer?.trim();

    if (answer.startsWith('<iframe')) {
        const match = answer.match(/srcdoc="([^"]*)"/);
        if (match && match[1]) {
            const temp = document.createElement('textarea');
            temp.innerHTML = match[1];
            return temp.value;
        }
    }
    
    return answer;
}

async function injectAnswer() {
    const thread = $(".thread-type-customer:first");
    const thread_id = thread.attr("data-thread_id");
    const mailbox_id = $("body").attr("data-mailbox_id");
    const customer_name = encodeURIComponent($(".customer-name").text());
    const customer_email = encodeURIComponent(
        $(".customer-email").text().trim()
    );
    const conversation_subject = encodeURIComponent(
        $(".conv-subjtext span").text().trim()
    );
    const conversation_id = $("body").attr("data-conversation_id");

    $("#bt-send-remote-response").addClass("hidden");
    $("#bt-sending-remote-response").removeClass("hidden");
    $(".rrbutton").addClass("disabled");

    fsAjax(
        `mailbox_id=${mailbox_id}&conversation_id=${conversation_id}&thread_id=${thread_id}&customer_name=${customer_name}&customer_email=${customer_email}&conversation_subject=${conversation_subject}`,
        "/ss-remote-response/generate",
        function (response) {
            $("#body").summernote(
                "pasteHTML",
                getHTMLFromAnswer(response.answer) ||
                    "Empty response from remote response module. Check your remote server response."
            );
            $("#bt-send-remote-response").removeClass("hidden");
            $("#bt-sending-remote-response").addClass("hidden");
            $(".rrbutton").removeClass("disabled");
        },
        true,
        function () {
            $("#bt-send-remote-response").removeClass("hidden");
            $("#bt-sending-remote-response").addClass("hidden");
            $(".rrbutton").removeClass("disabled");
            showFloatingAlert("error", Lang.get("messages.ajax_error"));
        }
    );
}
