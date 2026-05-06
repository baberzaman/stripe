(function () {
    var script = document.currentScript;
    var apiBase = (script && script.getAttribute('data-api-base')) || '/support-chat/api.php';

    var box = document.createElement('div');
    box.style.cssText = 'position:fixed;bottom:20px;right:20px;width:320px;background:#fff;border:1px solid #ccc;border-radius:8px;z-index:99999;font-family:Arial';
    box.innerHTML = '' +
        '<div style="padding:10px;background:#222;color:#fff;border-radius:8px 8px 0 0">Support Chat</div>' +
        '<div id="wLog" style="height:220px;overflow:auto;padding:10px"></div>' +
        '<div style="padding:10px;border-top:1px solid #eee">' +
        '<input id="wName" placeholder="Name" style="width:100%;margin-bottom:6px">' +
        '<input id="wEmail" placeholder="Email" style="width:100%;margin-bottom:6px">' +
        '<textarea id="wMsg" placeholder="Message" style="width:100%;height:50px"></textarea>' +
        '<button id="wSend" style="width:100%;margin-top:6px">Send</button>' +
        '</div>';

    document.body.appendChild(box);

    var ticketId = null;

    function log(text, sender) {
        var div = document.createElement('div');
        div.style.marginBottom = '6px';
        div.innerHTML = '<strong>' + sender + ':</strong> ' + text;
        box.querySelector('#wLog').appendChild(div);
        box.querySelector('#wLog').scrollTop = 999999;
    }

    function fetchMessages() {
        if (!ticketId) return;
        $.getJSON(apiBase, { action: 'get_messages', ticket_id: ticketId }, function (res) {
            var html = '';
            res.messages.forEach(function (m) {
                html += '<div style="margin-bottom:6px"><strong>' + m.sender + ':</strong> ' + $('<div>').text(m.message).html() + '</div>';
            });
            box.querySelector('#wLog').innerHTML = html;
        });
    }

    box.querySelector('#wSend').addEventListener('click', function () {
        var name = box.querySelector('#wName').value;
        var email = box.querySelector('#wEmail').value;
        var msg = box.querySelector('#wMsg').value;

        if (!ticketId) {
            $.ajax({
                url: apiBase + '?action=create_ticket',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ name: name, email: email, subject: 'Widget Chat', message: msg }),
                success: function (res) {
                    ticketId = res.ticket_id;
                    log($('<div>').text(msg).html(), 'customer');
                    box.querySelector('#wMsg').value = '';
                }
            });
            return;
        }

        $.post(apiBase + '?action=send_message', { ticket_id: ticketId, sender: 'customer', message: msg }, function () {
            log($('<div>').text(msg).html(), 'customer');
            box.querySelector('#wMsg').value = '';
            fetchMessages();
        }, 'json');
    });

    setInterval(fetchMessages, 4000);
})();
