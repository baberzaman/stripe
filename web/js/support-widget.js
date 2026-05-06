(function () {
  var config = window.SupportWidgetConfig || {};
  var siteKey = config.siteKey || 'demo-site';
  var apiBase = config.apiBase || (window.location.origin + '/api/support');
  var visitorId = localStorage.getItem('support_visitor_id');

  if (!visitorId) {
    visitorId = 'v_' + Math.random().toString(36).slice(2);
    localStorage.setItem('support_visitor_id', visitorId);
  }

  var root = document.createElement('div');
  root.style.position = 'fixed'; root.style.right = '20px'; root.style.bottom = '20px'; root.style.zIndex = '9999';
  root.innerHTML = '<button id="sw-toggle" style="background:#0a74ff;color:#fff;border:0;padding:10px 14px;border-radius:20px;cursor:pointer">Support</button>'+
    '<div id="sw-panel" style="display:none;width:320px;height:420px;background:#fff;border:1px solid #ddd;border-radius:8px;box-shadow:0 8px 20px rgba(0,0,0,.15);margin-top:8px">'+
    '<div style="display:flex"><button id="sw-chat-tab" style="flex:1;padding:10px">Chat</button><button id="sw-ticket-tab" style="flex:1;padding:10px">Ticket</button></div>'+
    '<div id="sw-chat-view" style="padding:10px;height:370px;display:flex;flex-direction:column"><div id="sw-chat-log" style="flex:1;overflow:auto;border:1px solid #eee;padding:8px;margin-bottom:8px"></div><div><input id="sw-chat-input" placeholder="Type message" style="width:75%"><button id="sw-send">Send</button></div></div>'+
    '<div id="sw-ticket-view" style="display:none;padding:10px"><input id="sw-name" placeholder="Name" style="width:100%;margin-bottom:6px"><input id="sw-email" placeholder="Email" style="width:100%;margin-bottom:6px"><input id="sw-subject" placeholder="Subject" style="width:100%;margin-bottom:6px"><textarea id="sw-description" placeholder="Issue" style="width:100%;height:130px;margin-bottom:6px"></textarea><button id="sw-ticket-submit">Submit Ticket</button><div id="sw-ticket-result" style="margin-top:8px"></div></div>'+
    '</div>';
  document.body.appendChild(root);

  function toggle() { var p = document.getElementById('sw-panel'); p.style.display = p.style.display === 'none' ? 'block':'none'; }
  function esc(s){ return (s||'').replace(/[&<>\"]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];}); }
  function renderMessages(messages){
    var log = document.getElementById('sw-chat-log');
    log.innerHTML = messages.map(function(m){return '<div><strong>'+esc(m.sender)+':</strong> '+esc(m.message)+'</div>';}).join('');
    log.scrollTop = log.scrollHeight;
  }

  function fetchMessages(){
    fetch(apiBase + '/messages?siteKey=' + encodeURIComponent(siteKey) + '&visitorId=' + encodeURIComponent(visitorId))
      .then(function(r){return r.json();})
      .then(function(data){renderMessages(data.messages || []);});
  }

  document.getElementById('sw-toggle').onclick = function(){toggle(); fetchMessages();};
  document.getElementById('sw-chat-tab').onclick = function(){document.getElementById('sw-chat-view').style.display='flex';document.getElementById('sw-ticket-view').style.display='none';};
  document.getElementById('sw-ticket-tab').onclick = function(){document.getElementById('sw-chat-view').style.display='none';document.getElementById('sw-ticket-view').style.display='block';};
  document.getElementById('sw-send').onclick = function(){
    var input = document.getElementById('sw-chat-input');
    fetch(apiBase + '/messages', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({siteKey:siteKey, visitorId:visitorId, sender:'visitor', message: input.value})})
      .then(function(){input.value=''; fetchMessages();});
  };
  document.getElementById('sw-ticket-submit').onclick = function(){
    var payload = {
      siteKey: siteKey,
      name: document.getElementById('sw-name').value,
      email: document.getElementById('sw-email').value,
      subject: document.getElementById('sw-subject').value,
      description: document.getElementById('sw-description').value
    };
    fetch(apiBase + '/tickets', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload)})
      .then(function(r){return r.json();})
      .then(function(data){document.getElementById('sw-ticket-result').innerText='Ticket #' + data.ticketId + ' submitted';});
  };

  setInterval(fetchMessages, 5000);
})();
