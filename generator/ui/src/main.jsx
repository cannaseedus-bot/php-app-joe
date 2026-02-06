import React, { useState } from "react";
import { createRoot } from "react-dom/client";

const defaults = {
  APP_NAME: "CitiBiz",
  TOKEN_NAME: "CITI",
  TOKEN_SYMBOL: "CITI",
  LOGO_URL: "/logo.png",
  GOOGLE_CLIENT_ID: "",
  CHAIN: "sepolia",
  FRAMEWORK: "react",
  BACKEND: "codeigniter",
  APP_DIR: "$HOME/citibiz2/out"
};

function App(){
  const [cfg,setCfg] = useState({...defaults});
  const [mode,setMode] = useState("local"); // 'local' or 'hosted'
  const [result,setResult] = useState(null);

  function onChange(e){
    setCfg({...cfg, [e.target.name]: e.target.value});
  }

  async function generate(){
    const payload = { ...cfg, xjson: "1.0", name: "CitiBiz2 Crypto Payments App", version: "0.2.0" };
    if(mode==="local"){
      // POST to local API (writes file to disk)
      const r = await fetch("http://localhost:4637/api/generate", {
        method:"POST", headers:{ "Content-Type":"application/json" }, body: JSON.stringify(payload)
      });
      const data = await r.json();
      setResult({
        message: "🛰️ Blueprint written locally.",
        path: data.path,
        next: [
          `cd ~/citibiz2`,
          `./install_from_json.sh ${data.path}`
        ]
      });
    } else {
      // Hosted mode → download JSON and show instructions
      const blob = new Blob([JSON.stringify(payload,null,2)], {type:"application/json"});
      const url = URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url; a.download = "citibiz_blueprint.json"; a.click();
      URL.revokeObjectURL(url);
      setResult({
        message: "⬇️ Downloaded blueprint (citibiz_blueprint.json).",
        next: [
          `cd ~/citibiz2`,
          `./install_from_json.sh ./citibiz_blueprint.json`
        ]
      });
    }
  }

  return (
    <div className="wrap">
      <h1>🛸 CitiBiz App Generator</h1>
      <p className="muted">Sci-fi installer UI — “Initializing memory implant…”</p>

      <div className="card mt">
        <div className="row">
          <div><label>App Name</label><input name="APP_NAME" value={cfg.APP_NAME} onChange={onChange}/></div>
          <div><label>Logo URL</label><input name="LOGO_URL" value={cfg.LOGO_URL} onChange={onChange}/></div>
        </div>
        <div className="row mt">
          <div><label>Token Name</label><input name="TOKEN_NAME" value={cfg.TOKEN_NAME} onChange={onChange}/></div>
          <div><label>Token Symbol</label><input name="TOKEN_SYMBOL" value={cfg.TOKEN_SYMBOL} onChange={onChange}/></div>
        </div>
        <div className="row mt">
          <div><label>Google Client ID</label><input name="GOOGLE_CLIENT_ID" value={cfg.GOOGLE_CLIENT_ID} onChange={onChange}/></div>
          <div><label>Chain</label>
            <select name="CHAIN" value={cfg.CHAIN} onChange={onChange}>
              <option>sepolia</option><option>base</option><option>arbitrum</option><option>polygon</option>
            </select>
          </div>
        </div>
        <div className="row mt">
          <div><label>Framework</label>
            <select name="FRAMEWORK" value={cfg.FRAMEWORK} onChange={onChange}>
              <option>react</option><option>next</option><option>vue</option><option>html</option>
            </select>
          </div>
          <div><label>Backend</label>
            <select name="BACKEND" value={cfg.BACKEND} onChange={onChange}>
              <option>codeigniter</option><option>firebase</option><option>supabase</option><option>none</option>
            </select>
          </div>
        </div>
        <div className="mt"><label>Output Directory</label><input name="APP_DIR" value={cfg.APP_DIR} onChange={onChange}/></div>

        <div className="mt"><label>Mode</label>
          <div className="row">
            <button type="button" style={{background: mode==='local'?'#22d3ee':'#1e293b'}} onClick={()=>setMode('local')}>Local (writes file, easiest)</button>
            <button type="button" style={{background: mode==='hosted'?'#22d3ee':'#1e293b'}} onClick={()=>setMode('hosted')}>Hosted (downloads JSON)</button>
          </div>
        </div>

        <div className="mt">
          <button onClick={generate}>🚀 Generate Blueprint</button>
        </div>

        {result && (
          <div className="mt">
            <p className="ok">{result.message}</p>
            {result.path && <p className="muted">Path: <code>{result.path}</code></p>}
            <pre className="mt" style={{whiteSpace:'pre-wrap',background:'#0b1220',padding:'12px',borderRadius:'8px',border:'1px solid #1f2937'}}>
{result.next.join('\n')}
            </pre>
          </div>
        )}
      </div>
    </div>
  );
}

createRoot(document.getElementById("root")).render(<App/>);
