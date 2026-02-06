import express from "express";
import cors from "cors";
import fs from "fs";
import path from "path";
import os from "os";

const app = express();
app.use(cors());
app.use(express.json({limit:"2mb"}));

const HOME = os.homedir();
const BASE = path.join(HOME, "citibiz2", "generated");

app.post("/api/generate", (req,res)=>{
  const b = req.body || {};
  const dir = (b.APP_DIR || `${HOME}/citibiz2/out`).replace(/^~\//, `${HOME}/`);
  const outDir = path.join(BASE, Date.now().toString());
  fs.mkdirSync(outDir, {recursive:true});

  // minimal JSON-OS blueprint using your fields; installer already knows defaults & templates
  const blueprint = {
    xjson:"1.0",
    name:"CitiBiz2 Crypto Payments App",
    version:"0.2.0",
    platform:"gitbash",
    defaults: {
      APP_NAME: b.APP_NAME, TOKEN_NAME: b.TOKEN_NAME, TOKEN_SYMBOL: b.TOKEN_SYMBOL,
      LOGO_URL: b.LOGO_URL, GOOGLE_CLIENT_ID: b.GOOGLE_CLIENT_ID, CHAIN: b.CHAIN,
      APP_DIR: b.APP_DIR, FRAMEWORK: b.FRAMEWORK, BACKEND: b.BACKEND
    }
  };

  const file = path.join(outDir, "blueprint.json");
  fs.writeFileSync(file, JSON.stringify(blueprint,null,2));
  res.json({ ok:true, path: file.replace(/\\/g,'/') });
});

const PORT = 4637;
app.listen(PORT, ()=> console.log(`🛰️ Generator API online at http://localhost:${PORT}`));
