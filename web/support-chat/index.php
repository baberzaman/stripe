<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Core PHP Ticket + Chat</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body{font-family:Arial, sans-serif;max-width:900px;margin:30px auto;padding:0 16px}
        input,textarea,button{padding:8px;margin:4px 0;width:100%}
        #chat{border:1px solid #ccc;padding:10px;height:240px;overflow:auto;margin:10px 0}
        .msg{margin:6px 0;padding:6px;border-radius:6px}
        .customer{background:#e7f5ff}
        .agent{background:#f4f4f4}
        .grid{display:grid;grid-template-columns:1fr 1fr;gap:24px}
    </style>
</head>
<body>
<h1>Ticket and Chat System (Core PHP + jQuery)</h1>
<div class="grid">
<section>
    <h2>Create Ticket</h2>
    <input id="name" placeholder="Name">
    <input id="email" placeholder="Email">
    <input id="subject" placeholder="Subject">
    <textarea id="firstMessage" placeholder="Describe your issue"></textarea>
    <button id="createTicket">Create Ticket</button>
    <p>Current Ticket ID: <strong id="ticketId">-</strong></p>
</section>
<section>
    <h2>Chat</h2>
    <div id="chat"></div>
    <textarea id="chatMessage" placeholder="Type message"></textarea>
    <button id="sendCustomer">Send as Customer</button>
    <button id="sendAgent">Send as Agent</button>
</section>
</div>

<h3>Embeddable Widget Script</h3>
<pre>&lt;script src="/support-chat/widget.js" data-api-base="/support-chat/api.php"&gt;&lt;/script&gt;</pre>

<script>
let currentTicket = null;

function loadMessages() {
    if (!currentTicket) return;
    $.getJSON('api.php', {action: 'get_messages', ticket_id: currentTicket}, function (res) {
        $('#chat').html('');
        res.messages.forEach(function (m) {
            $('#chat').append('<div class="msg '+m.sender+'"><strong>'+m.sender+':</strong> '+$('<div>').text(m.message).html()+'</div>');
        });
        $('#chat').scrollTop($('#chat')[0].scrollHeight);
    });
}

$('#createTicket').on('click', function () {
    $.ajax({
        url: 'api.php?action=create_ticket',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            name: $('#name').val(),
            email: $('#email').val(),
            subject: $('#subject').val(),
            message: $('#firstMessage').val()
        }),
        success: function (res) {
            currentTicket = res.ticket_id;
            $('#ticketId').text(currentTicket);
            loadMessages();
        },
        error: function (xhr) {
            alert(xhr.responseText);
        }
    });
});

function send(sender) {
    if (!currentTicket) return alert('Create a ticket first.');
    $.post('api.php?action=send_message', {
        ticket_id: currentTicket,
        sender: sender,
        message: $('#chatMessage').val()
    }, function () {
        $('#chatMessage').val('');
        loadMessages();
    }, 'json');
}

$('#sendCustomer').on('click', function(){ send('customer'); });
$('#sendAgent').on('click', function(){ send('agent'); });
setInterval(loadMessages, 3000);
</script>
</body>
</html>
