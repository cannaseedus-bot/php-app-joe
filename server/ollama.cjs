
const http = require('http');

function stream(reqBody, res) {
  const r = http.request(
    { host:'127.0.0.1', port:11434, path:'/api/chat', method:'POST', headers:{'Content-Type':'application/json'} },
    (up) => {
      res.writeHead(up.statusCode, up.headers);
      up.on('data', c => res.write(c));
      up.on('end', ()=>res.end());
    }
  );
  r.on('error', e => {
    res.writeHead(500, {'Content-Type':'application/json'});
    res.end(JSON.stringify({error:String(e)}));
  });
  r.write(JSON.stringify(reqBody));
  r.end();
}
module.exports = { stream };
