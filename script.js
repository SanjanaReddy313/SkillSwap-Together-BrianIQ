const DEMO_USERS = [
  {id:1,name:"jay",email:"jay@skillswap.com",password:"123456",offer:["React","Js"],want:["Python"],availability:"available",rating:0},
  {id:2,name:"harsh",email:"harsh@skillswap.com",password:"123456",offer:["Tailwind CSS","Js"],want:["React"],availability:"available",rating:0},
  {id:3,name:"Hiya",email:"hiya@skillswap.com",password:"123456",offer:["Python","Js"],want:["React"],availability:"available",rating:0},
  {id:4,name:"Ayush Thakkar",email:"ayush@skillswap.com",password:"123456",offer:["Python","Js"],want:["Java"],availability:"available",rating:0},
  {id:5,name:"Heer Trivedi",email:"heer@skillswap.com",password:"123456",offer:["Python","Js"],want:["React"],availability:"available",rating:0}
];

function getUsers(){
  const saved=JSON.parse(localStorage.getItem("skillSwapUsers")||"[]");
  const map=new Map(DEMO_USERS.map(u=>[u.email.toLowerCase(),u]));
  saved.forEach(u=>map.set(u.email.toLowerCase(),u));
  return [...map.values()];
}
function saveUsers(users){localStorage.setItem("skillSwapUsers",JSON.stringify(users))}
function setCurrentUser(user){localStorage.setItem("currentUser",JSON.stringify(user))}
function currentUser(){return JSON.parse(localStorage.getItem("currentUser")||"null")}

function togglePassword(id){
  const el=document.getElementById(id);
  if(el) el.type=el.type==="password"?"text":"password";
}

function demoLogin(type){
  const user=type==="admin"
    ? {id:99,name:"Demo Admin",email:"admin@skillswap.com",password:"admin123",offer:["Management","JavaScript"],want:["Python"],availability:"available",rating:0}
    : DEMO_USERS[0];
  setCurrentUser(user);
  window.location.href="dashboard.html";
}

function forgotPassword(e){
  e.preventDefault();
  const email=prompt("Enter your registered email address:");
  if(!email) return;
  const found=getUsers().find(u=>u.email.toLowerCase()===email.toLowerCase());
  alert(found ? "Password reset link demo: please contact the administrator." : "Email address not found.");
}

document.addEventListener("DOMContentLoaded",()=>{
  const loginForm=document.getElementById("loginForm");
  if(loginForm){
    loginForm.addEventListener("submit",e=>{
      e.preventDefault();
      const email=document.getElementById("loginEmail").value.trim().toLowerCase();
      const password=document.getElementById("loginPassword").value;
      const user=getUsers().find(u=>u.email.toLowerCase()===email && u.password===password);
      const msg=document.getElementById("loginMessage");
      if(!user){msg.textContent="Invalid email or password.";return}
      setCurrentUser(user);
      window.location.href="dashboard.html";
    });
  }

  const signupForm=document.getElementById("signupForm");
  if(signupForm){
    signupForm.addEventListener("submit",e=>{
      e.preventDefault();
      const name=document.getElementById("signupName").value.trim();
      const email=document.getElementById("signupEmail").value.trim().toLowerCase();
      const password=document.getElementById("signupPassword").value;
      const confirm=document.getElementById("signupConfirm").value;
      const offer=document.getElementById("signupOffer").value.split(",").map(x=>x.trim()).filter(Boolean);
      const want=document.getElementById("signupWant").value.split(",").map(x=>x.trim()).filter(Boolean);
      const msg=document.getElementById("signupMessage");
      if(password!==confirm){msg.textContent="Passwords do not match.";return}
      const users=getUsers();
      if(users.some(u=>u.email.toLowerCase()===email)){msg.textContent="An account with this email already exists.";return}
      const user={id:Date.now(),name,email,password,offer,want,availability:"available",rating:0};
      const saved=JSON.parse(localStorage.getItem("skillSwapUsers")||"[]");
      saved.push(user); saveUsers(saved); setCurrentUser(user);
      msg.textContent="Account created successfully!";
      setTimeout(()=>window.location.href="dashboard.html",600);
    });
  }

  const name=document.getElementById("dashName");
  if(name){
    const u=currentUser();
    if(!u){window.location.href="login.html";return}
    name.textContent=u.name;
    const nav=document.getElementById("dashboardNav");
    if(nav) nav.innerHTML='<a class="nav-btn secondary" href="discover.html">Discover</a><a class="nav-btn primary" href="profile.html">Profile</a>';
  }

  if(document.getElementById("userGrid")) renderUsers();
  if(document.getElementById("profileName")) loadProfile();
});

function renderUsers(){
  const grid=document.getElementById("userGrid");
  if(!grid)return;
  const query=(document.getElementById("searchInput")?.value||"").toLowerCase();
  const availability=document.getElementById("availability")?.value||"all";
  const users=getUsers().filter(u=>{
    const text=[u.name,...(u.offer||[]),...(u.want||[])].join(" ").toLowerCase();
    return (!query||text.includes(query))&&(availability==="all"||u.availability===availability);
  });
  document.getElementById("resultCount").textContent=`Showing ${users.length} of ${getUsers().length} users`;
  if(!users.length){grid.innerHTML='<div class="empty">No users found. Try another skill or name.</div>';return}
  grid.innerHTML=users.map(u=>`
    <article class="user-card">
      <div class="user-top">
        <div class="user-avatar">◎</div>
        <div class="user-name">${escapeHTML(u.name)}</div>
        <div class="rating">★ ${u.rating||0}</div>
      </div>
      <div class="label">SKILLS OFFERED</div>
      <div class="skill-list">${(u.offer||[]).map(s=>`<span class="skill">${escapeHTML(s)}</span>`).join("")||"<span class='available'>None added</span>"}</div>
      <div class="label">SKILLS WANTED</div>
      <div class="skill-list">${(u.want||[]).map(s=>`<span class="skill">${escapeHTML(s)}</span>`).join("")||"<span class='available'>None added</span>"}</div>
      <div class="label">AVAILABLE</div>
      <div class="available">${u.availability==="available"?"Available for skill swap":"Currently busy"}</div>
      <div class="card-actions">
        <button class="card-btn" onclick="viewProfile(${u.id})">View Profile</button>
        <button class="card-btn primary" onclick="requestSwap(${u.id})">Request</button>
      </div>
    </article>`).join("");
}
function viewProfile(id){
  const u=getUsers().find(x=>x.id===id);
  if(!u)return;
  alert(`${u.name}\n\nSkills Offered: ${(u.offer||[]).join(", ")||"None"}\nSkills Wanted: ${(u.want||[]).join(", ")||"None"}\nAvailability: ${u.availability}`);
}
function requestSwap(id){
  const u=getUsers().find(x=>x.id===id);
  if(!u)return;
  const me=currentUser();
  if(!me){alert("Please login before sending a request.");window.location.href="login.html";return}
  alert(`Skill request sent to ${u.name}!`);
}
function loadProfile(){
  const u=currentUser();
  if(!u){window.location.href="login.html";return}
  document.getElementById("profileName").textContent=u.name;
  document.getElementById("profileEmail").textContent=u.email;
  document.getElementById("profileOffer").innerHTML=(u.offer||[]).map(s=>`<span class="skill">${escapeHTML(s)}</span>`).join("")||"<span class='available'>No skills added</span>";
  document.getElementById("profileWant").innerHTML=(u.want||[]).map(s=>`<span class="skill">${escapeHTML(s)}</span>`).join("")||"<span class='available'>No skills added</span>";
}
function editProfile(){
  const u=currentUser(); if(!u)return;
  const name=prompt("Enter your name:",u.name); if(!name)return;
  const offer=prompt("Skills you offer (comma separated):",(u.offer||[]).join(", "));
  const want=prompt("Skills you want (comma separated):",(u.want||[]).join(", "));
  u.name=name.trim();
  u.offer=(offer||"").split(",").map(x=>x.trim()).filter(Boolean);
  u.want=(want||"").split(",").map(x=>x.trim()).filter(Boolean);
  setCurrentUser(u);
  const saved=JSON.parse(localStorage.getItem("skillSwapUsers")||"[]");
  const i=saved.findIndex(x=>x.id===u.id); if(i>=0){saved[i]=u;saveUsers(saved)}
  loadProfile();
}
function logout(){localStorage.removeItem("currentUser");window.location.href="index.html"}
function escapeHTML(str){return String(str).replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[c]))}