<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SurfZilla — PBKDF2 PWA with Auctions</title>
<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#0ea5a4">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { background:#f3f4f6; min-height:100vh; }
  .sidebar { background: #fff; padding: 1rem; border-radius: .5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
  .card { border-radius: .6rem; }
  .feed { max-height: 70vh; overflow:auto; }
  .small-muted { font-size: .85rem; color: #6b7280; }
  .avatar { width:40px;height:40px;border-radius:50%;object-fit:cover; }
  .like-btn.active { color:#e11d48; font-weight:700; }
  .dropzone { border:2px dashed #e5e7eb; padding:10px; border-radius:8px; text-align:center; background:#fff; cursor:pointer; }
  .dropzone.bg-light { background:#f3f4f6; }
  .blocked { opacity:0.5; }
  iframe, video { max-width:100%; border-radius:6px; }
</style>
</head>
<body>

<div class="container-fluid py-3">
  <div class="row g-3">
    <!-- LEFT -->
    <div class="col-12 col-md-3">
      <div class="sidebar">
        <h4 class="mb-3">SurfZilla</h4>
        <nav class="nav flex-column mb-3">
          <a class="nav-link" href="#" id="navHome">Home</a>
          <a class="nav-link" href="#" id="navExplore">Explore</a>
          <a class="nav-link" href="#" id="navNotifications">Notifications</a>
          <a class="nav-link" href="#" id="navMessages">Messages</a>
        </nav>

        <div id="userPanel" class="mt-3"></div>

        <hr>
        <div>
          <small class="text-muted">Offline-first: data stored locally in your browser. Export your profile to keep a copy.</small>
        </div>
      </div>
    </div>

    <!-- CENTER -->
    <div class="col-12 col-md-6">
      <div id="centerContent"></div>
    </div>

    <!-- RIGHT -->
    <div class="col-12 col-md-3 d-none d-md-block">
      <div class="sidebar mb-3">
        <h6>People (profiles)</h6>
        <ul id="peopleOnline" class="list-unstyled mb-0"></ul>
      </div>
      <div class="sidebar">
        <h6>Tools</h6>
        <button id="openSettings" class="btn btn-sm btn-outline-secondary w-100 mb-2">Settings</button>
        <button id="exportAllBtn" class="btn btn-sm btn-outline-primary w-100 mb-2">Export All Data</button>
        <label class="btn btn-sm btn-outline-secondary w-100 mb-2">
          Import JSON <input id="importJsonFile" type="file" accept="application/json" hidden>
        </label>
        <hr>
        <h6>Trends</h6>
        <ul id="trends" class="list-unstyled mb-0">
          <li>#SurfZilla</li>
          <li>#LocalPWA</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- TEMPLATES -->
<template id="loginTemplate">
  <div class="card p-4">
    <h5>Login</h5>
    <div class="mb-2">
      <label class="form-label">Username</label>
      <input id="loginUsername" class="form-control" />
    </div>
    <div class="mb-2">
      <label class="form-label">Password</label>
      <input id="loginPassword" type="password" class="form-control" />
    </div>
    <div class="d-flex gap-2">
      <button id="doLogin" class="btn btn-primary">Login</button>
      <button id="showRegister" class="btn btn-outline-secondary">Register</button>
    </div>
    <div id="loginMsg" class="mt-2 small text-danger"></div>
  </div>
</template>

<template id="registerTemplate">
  <div class="card p-4">
    <h5>Create account</h5>
    <div class="mb-2">
      <label class="form-label">Username</label>
      <input id="regUsername" class="form-control" />
    </div>
    <div class="mb-2">
      <label class="form-label">Display name</label>
      <input id="regDisplay" class="form-control" />
    </div>
    <div class="mb-2">
      <label class="form-label">Avatar Upload (drag & drop or choose)</label>
      <div class="dropzone" id="avatarDrop">Drop image here or click to choose<input id="regAvatarFile" type="file" accept="image/*" style="display:none"></div>
      <input id="regAvatarUrl" class="form-control mt-2" placeholder="Optional remote avatar URL">
    </div>
    <div class="mb-2">
      <label class="form-label">Password</label>
      <input id="regPassword" type="password" class="form-control" />
    </div>
    <div class="d-flex gap-2">
      <button id="doRegister" class="btn btn-success">Create</button>
      <button id="backToLogin" class="btn btn-outline-secondary">Back</button>
    </div>
    <div id="regMsg" class="mt-2 small text-danger"></div>
  </div>
</template>

<template id="profilePanelTemplate">
  <div class="d-flex align-items-center gap-2">
    <img id="profileAvatar" class="avatar" src="" alt="avatar">
    <div>
      <div id="profileName" class="fw-bold"></div>
      <div class="small-muted">Logged in</div>
    </div>
    <div class="ms-auto d-flex gap-2">
      <button id="openProfileSettings" class="btn btn-sm btn-outline-secondary">Profile</button>
      <button id="logoutBtn" class="btn btn-sm btn-outline-danger">Logout</button>
    </div>
  </div>
</template>

<template id="postFormTemplate">
  <div class="card p-3 mb-3">
    <form id="postFormLocal">
      <div class="mb-2">
        <input id="postTitle" class="form-control" placeholder="Title (optional)" />
      </div>
      <div class="mb-2">
        <textarea id="postContent" class="form-control" rows="3" placeholder="Share something..."></textarea>
      </div>
      <div class="mb-2 row g-2">
        <div class="col-12 col-md-6">
          <input id="postImage" class="form-control" placeholder="Image URL (optional)" />
        </div>
        <div class="col-12 col-md-6">
          <input id="postVideo" class="form-control" placeholder="Video URL (YouTube link or raw video)" />
        </div>
      </div>
      <div class="d-flex gap-2">
        <select id="postType" class="form-select w-auto">
          <option value="text">Text</option>
          <option value="image">Image</option>
          <option value="video">Video</option>
          <option value="link">Link</option>
          <option value="story">Story</option>
          <option value="news">News</option>
          <option value="blog">Blog</option>
          <option value="auction">Auction</option>
        </select>
        <button class="btn btn-primary" type="submit">Post</button>
      </div>
    </form>
  </div>
</template>

<template id="postCardTemplate">
  <div class="card p-3 mb-3">
    <div class="d-flex align-items-center mb-2">
      <img class="avatar me-2" src="" alt="av">
      <div>
        <div class="fw-bold authorName"></div>
        <div class="small-muted postMeta"></div>
      </div>
      <div class="ms-auto postControls"></div>
    </div>
    <h6 class="postTitle"></h6>
    <div class="postContent"></div>
    <div class="postMedia mt-2"></div>
    <!-- Auction section -->
    <div class="d-flex gap-2 mt-2 auction-section" style="display:none;">
      <span class="current-bid fw-bold">Current bid: $0</span>
      <input type="number" class="form-control form-control-sm bid-input" placeholder="Your bid" style="width:100px;">
      <button class="btn btn-sm btn-success place-bid-btn">Place Bid</button>
    </div>
    <div class="d-flex gap-2 mt-3">
      <button class="btn btn-sm btn-outline-primary like-btn">Like <span class="like-count ms-1">0</span></button>
      <button class="btn btn-sm btn-outline-secondary toggle-comments">Comments <span class="comment-count ms-1">0</span></button>
    </div>
    <div class="commentsSection mt-2" style="display:none;">
      <div class="mb-2 comment-list"></div>
      <div class="input-group">
        <input class="form-control comment-input" placeholder="Write a comment...">
        <button class="btn btn-primary btn-add-comment">Comment</button>
      </div>
    </div>
  </div>
</template>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const $ = id => document.getElementById(id);

let currentUser = null;
let posts = [];
let users = JSON.parse(localStorage.getItem('users')||'{}');

function saveData(key,data){localStorage.setItem(key,JSON.stringify(data));}
function loadData(key){return JSON.parse(localStorage.getItem(key)||'[]');}

posts = loadData('posts');
currentUser = JSON.parse(localStorage.getItem('currentUser')||'null');

// --- Dropzone utility ---
function setupDropzone(dropElem, fileInput, callback){
  dropElem.addEventListener('click',()=>fileInput.click());
  dropElem.addEventListener('dragover',e=>e.preventDefault());
  dropElem.addEventListener('drop',e=>{
    e.preventDefault();
    const file = e.dataTransfer.files[0]; 
    if(!file) return;
    const reader = new FileReader();
    reader.onload = ()=>callback(reader.result);
    reader.readAsDataURL(file);
  });
  fileInput.addEventListener('change',()=> {
    const file = fileInput.files[0]; if(!file) return;
    const reader = new FileReader();
    reader.onload = ()=>callback(reader.result);
    reader.readAsDataURL(file);
  });
}

function renderUserPanel(){
  const panel = $('userPanel'); panel.innerHTML='';
  if(!currentUser){
    const tmpl = $('loginTemplate').content.cloneNode(true);
    panel.appendChild(tmpl);
  } else {
    const tmpl = $('profilePanelTemplate').content.cloneNode(true);
    panel.appendChild(tmpl);
    $('profileAvatar').src = currentUser.avatar||'https://via.placeholder.com/40';
    $('profileName').innerText = currentUser.displayName||currentUser.username;
    $('logoutBtn').onclick = ()=>{
      currentUser = null;
      localStorage.removeItem('currentUser');
      renderUserPanel();
      renderFeed();
    };
  }
}

function renderFeed(){
  const center = $('centerContent'); center.innerHTML='';
  const formTmpl = $('postFormTemplate').content.cloneNode(true);
  center.appendChild(formTmpl);

  $('postFormLocal').onsubmit = e=>{
    e.preventDefault();
    if(!currentUser) return alert('Login first');
    const post = {
      id:Date.now(),
      author:currentUser.username,
      title:$('postTitle').value.trim(),
      content:$('postContent').value.trim(),
      image:$('postImage').value.trim(),
      video:$('postVideo').value.trim(),
      type:$('postType').value,
      likes:0,
      comments:[],
      auction:{currentBid:0,bids:[]},
      createdAt:new Date().toISOString()
    };
    posts.unshift(post);
    saveData('posts',posts);
    renderFeed();
  };

  posts.forEach(p=>{
    const tmpl = $('postCardTemplate').content.cloneNode(true);
    tmpl.querySelector('.authorName').innerText = p.author;
    tmpl.querySelector('.postMeta').innerText = new Date(p.createdAt).toLocaleString();
    tmpl.querySelector('.postTitle').innerText = p.title;
    tmpl.querySelector('.postContent').innerText = p.content;
    if(p.image) tmpl.querySelector('.postMedia').innerHTML=`<img src="${p.image}" class="img-fluid rounded">`;
    if(p.video) tmpl.querySelector('.postMedia').innerHTML=`<iframe src="${p.video}" frameborder="0" allowfullscreen></iframe>`;

    // Like button
    const likeBtn = tmpl.querySelector('.like-btn');
    likeBtn.onclick = ()=>{
      p.likes++; likeBtn.querySelector('.like-count').innerText=p.likes;
      saveData('posts',posts);
    };

    // Comments
    const toggleComments = tmpl.querySelector('.toggle-comments');
    const commentSection = tmpl.querySelector('.commentsSection');
    toggleComments.onclick = ()=>commentSection.style.display = commentSection.style.display==='none'?'block':'none';
    const addCommentBtn = tmpl.querySelector('.btn-add-comment');
    const commentInput = tmpl.querySelector('.comment-input');
    const commentList = tmpl.querySelector('.comment-list');
    p.comments.forEach(c=>{
      const div = document.createElement('div'); div.innerText=c; commentList.appendChild(div);
    });
    addCommentBtn.onclick=()=>{
      const val = commentInput.value.trim(); if(!val) return;
      p.comments.push(val);
      saveData('posts',posts);
      renderFeed();
    };

    // Auction
    if(p.type==='auction'){
      const auctionDiv = tmpl.querySelector('.auction-section');
      auctionDiv.style.display='flex';
      const bidInput = auctionDiv.querySelector('.bid-input');
      const currentBidSpan = auctionDiv.querySelector('.current-bid');
      const bidBtn = auctionDiv.querySelector('.place-bid-btn');

      function updateAuctionDisplay(){
        currentBidSpan.innerText=`Current bid: $${p.auction.currentBid}`;
        bidInput.value='';
      }
      updateAuctionDisplay();

      bidBtn.onclick = ()=>{
        const val = parseFloat(bidInput.value);
        if(isNaN(val)||val<=p.auction.currentBid) return alert('Bid must be higher than current bid');
        p.auction.currentBid = val;
        p.auction.bids.push({user:currentUser.username, amount:val, time:new Date().toISOString()});
        saveData('posts',posts);
        updateAuctionDisplay();
      };
    }

    tmpl.querySelector('.postControls').innerHTML=`<button class="btn btn-sm btn-outline-secondary" onclick='alert("Edit demo")'>Edit</button>`;
    center.appendChild(tmpl);
  });
}

renderUserPanel();
renderFeed();
</script>
</body>
</html>
