/* ===================== DATA (injected from MySQL via PHP, see index.php) ===================== */
const destinations = window.HC_DESTINATIONS || [];
const packages      = window.HC_PACKAGES || [];

const guides = [
  {tag:"Getting Around", image:"assets/images/guides/getting-around.jpg", title:"Should you hire a driver or self-drive?", desc:"Why almost every visitor ends up with a private driver-guide — and what it actually costs per day.", grad:"linear-gradient(155deg,#0E2C4C,#3FA9E8)"},
  {tag:"Best Time to Visit", image:"assets/images/guides/best-time.jpg", title:"Sri Lanka has two monsoons, not one", desc:"How to pick a coast (or the hills) depending on when you're travelling.", grad:"linear-gradient(155deg,#102A44,#7FC8F5)"},
  {tag:"Food", image:"assets/images/guides/food.jpg", title:"What to actually order at a rice & curry shop", desc:"A short, honest guide to eating well without a phrasebook.", grad:"linear-gradient(155deg,#0B2138,#2E9ED4)"}
];

const reviews = [
  {quote:"We handed them a rough list of places and got back an itinerary that made sense geographically — something we clearly weren't going to figure out ourselves in a week.", who:"Amara & Josh — Full Island Grand Tour"},
  {quote:"The Ella train seats were reserved exactly as promised, and our driver in Yala found leopards on both drives. Small details, handled well.", who:"Priyanka R. — Hill Country + Wildlife"},
  {quote:"Booking took five minutes and the reply came the same afternoon with a real quote, not a sales call. That alone stood out.", who:"Daniel K. — Southern Coast Getaway"}
];

const faqs = [
  {q:"How far in advance should I book?", a:"4–6 weeks is comfortable for most itineraries; during peak season (Dec–Mar) we'd suggest 2–3 months, especially for beach-facing stays in Mirissa and Galle."},
  {q:"Do prices include flights?", a:"No — package prices cover ground arrangements only (driver, stays, listed activities, and a guide where noted). We're happy to advise on flight routing separately."},
  {q:"Can packages be customized?", a:"Yes, every package on this page is a starting template. Tell us your dates, pace, and interests in the enquiry form and we'll adjust the route."},
  {q:"Is it safe to travel with kids or elderly parents?", a:"Sri Lanka is generally an easy, family-friendly destination. We adjust driving distances and hike difficulty for the group — mention ages in your enquiry."}
];

/* ===================== RENDER ===================== */
function iconMark(icon){return `<div class="icon-mark"><i class="fa-solid ${icon}"></i></div>`;}

function renderDestinations(list){
  const grid = document.getElementById('destGrid');
  if(!grid) return;
  if(!list.length){
    grid.innerHTML = '<p style="color:var(--paper-dim)">No destinations to show yet — add some from the admin panel.</p>';
    return;
  }
  grid.innerHTML = list.map(d => `
    <div class="dest-card" tabindex="0" role="button" data-region="${d.region}" data-name="${d.name.toLowerCase()}" data-tag="${d.tag.toLowerCase()}" data-text="${(d.desc||'').toLowerCase().replace(/"/g,'&quot;')}" data-dest="${d.name}">
      <div class="dest-photo" style="background:${destBg(d)}"></div>
      ${iconMark(d.icon)}
      <div class="dest-info">
        <div class="region">${d.region}</div>
        <h3>${d.name}</h3>
        <p>${d.desc}</p>
      </div>
    </div>`).join('');
}
renderDestinations(destinations);
(function(){
  const grid = document.getElementById('destGrid');
  if(!grid) return;
  grid.addEventListener('click', (e)=>{
    const card = e.target.closest('.dest-card');
    if(card) openDestModal(card.dataset.dest);
  });
  grid.addEventListener('keydown', (e)=>{
    if(e.key !== 'Enter' && e.key !== ' ') return;
    const card = e.target.closest('.dest-card');
    if(card){ e.preventDefault(); openDestModal(card.dataset.dest); }
  });
})();

/* ===================== DESTINATION MODAL ===================== */
/* Photo if the destination has one, otherwise the gradient fallback */
function destBg(d){
  return d.image ? `url('${d.image}') center/cover no-repeat, ${d.grad}` : d.grad;
}

function openDestModal(name){
  const d = destinations.find(x => x.name === name);
  if(!d) return;
  const overlay = document.getElementById('destModal');
  overlay.querySelector('.modal-photo').style.background = destBg(d);
  overlay.querySelector('.modal-icon').innerHTML = `<i class="fa-solid ${d.icon}"></i>`;
  overlay.querySelector('.modal-region').textContent = d.region;
  overlay.querySelector('.modal-title').textContent = d.name;
  overlay.querySelector('.modal-tag').textContent = d.tag;
  overlay.querySelector('.modal-desc').textContent = d.longDesc;
  overlay.querySelector('.fact-time').textContent = d.bestTime;
  overlay.querySelector('.fact-duration').textContent = d.duration;
  overlay.querySelector('.fact-highlight').textContent = d.highlight;
  overlay.querySelector('.fact-region').textContent = d.region;
  overlay.classList.add('open');
  document.body.classList.add('modal-locked');
}
function closeDestModal(){
  const m = document.getElementById('destModal');
  if(!m) return;
  m.classList.remove('open');
  document.body.classList.remove('modal-locked');
}
const destModalEl = document.getElementById('destModal');
if(destModalEl){
  destModalEl.addEventListener('click', (e)=>{ if(e.target.id === 'destModal') closeDestModal(); });
}
const modalCloseEl = document.getElementById('modalCloseBtn');
if(modalCloseEl){ modalCloseEl.addEventListener('click', closeDestModal); }
document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape') closeDestModal(); });

function renderPackages(){
  const scroller = document.getElementById('pkgScroller');
  if(!scroller) return;
  scroller.innerHTML = packages.map(p => `
    <div class="pkg-card">
      <div class="pkg-top"><h3>${p.name}</h3><span class="pkg-days">${p.days}</span></div>
      <p class="desc">${p.desc}</p>
      <ul class="pkg-list">${p.items.map(i=>`<li><i class="fa-solid fa-check"></i>${i}</li>`).join('')}</ul>
      <div class="pkg-foot"><div class="pkg-price">$${Number(p.price).toFixed(0)}<small> / person</small></div><a href="#booking" class="btn btn-ghost" style="padding:10px 18px; font-size:13px;" onclick="preselectPackage('${p.name.replace(/'/g, "\\'")}')">Enquire</a></div>
    </div>`).join('');
}
renderPackages();

function preselectPackage(name){
  const select = document.getElementById('fPkg');
  if(select){
    for(const opt of select.options){ if(opt.value === name){ select.value = name; break; } }
  }
}

function renderGuides(){
  const grid = document.getElementById('guideGrid');
  if(!grid) return;
  grid.innerHTML = guides.map(g => `
    <div class="guide-card">
      <div class="guide-photo" style="background:${g.image ? `url('${g.image}') center/cover no-repeat, ${g.grad}` : g.grad}"></div>
      <div class="guide-body">
        <div class="tag">${g.tag}</div>
        <h4>${g.title}</h4>
        <p>${g.desc}</p>
      </div>
    </div>`).join('');
}
renderGuides();

function renderFaq(){
  const list = document.getElementById('faqList');
  if(!list) return;
  list.innerHTML = faqs.map((f,i) => `
    <div class="faq-item" data-i="${i}">
      <button class="faq-q">${f.q}<i class="fa-solid fa-plus"></i></button>
      <div class="faq-a"><p>${f.a}</p></div>
    </div>`).join('');
  document.querySelectorAll('.faq-q').forEach(btn=>{
    btn.addEventListener('click', () => {
      const item = btn.closest('.faq-item');
      const answer = item.querySelector('.faq-a');
      const isOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(fi=>{fi.classList.remove('open'); fi.querySelector('.faq-a').style.maxHeight=null;});
      if(!isOpen){ item.classList.add('open'); answer.style.maxHeight = answer.scrollHeight + 'px'; }
    });
  });
}
renderFaq();

/* ===================== REVIEWS SLIDER ===================== */
function renderReviews(){
  const wrap = document.getElementById('reviewsWrap');
  if(!wrap) return;
  wrap.innerHTML = `
    <div class="slides">${reviews.map((r,i)=>`
      <div class="review-slide ${i===0?'active':''}" data-i="${i}">
        <div class="stars">★★★★★</div>
        <blockquote>"${r.quote}"</blockquote>
        <div class="review-who">${r.who}</div>
      </div>`).join('')}</div>
    <div class="review-dots">${reviews.map((_,i)=>`<button class="${i===0?'active':''}" data-i="${i}" aria-label="Review ${i+1}"></button>`).join('')}</div>
  `;
  let current = 0;
  const slides = wrap.querySelectorAll('.review-slide');
  const dots = wrap.querySelectorAll('.review-dots button');
  function show(i){
    slides.forEach(s=>s.classList.remove('active'));
    dots.forEach(d=>d.classList.remove('active'));
    slides[i].classList.add('active');
    dots[i].classList.add('active');
    current = i;
  }
  dots.forEach(d => d.addEventListener('click', ()=> show(parseInt(d.dataset.i))));
  setInterval(()=>{ show((current+1)%slides.length); }, 6000);
}
renderReviews();

/* ===================== SEARCH + FILTER ===================== */
const searchInput = document.getElementById('destSearch');
const chips = document.querySelectorAll('.chip');
let activeFilter = 'all';

const PAGE_SIZE = 3;            // cards shown first, and added on every "See more" click
let visibleCount = PAGE_SIZE;
const moreWrap = document.getElementById('destMore');
const moreBtn  = document.getElementById('destMoreBtn');

function applyFilters(){
  const q = searchInput ? searchInput.value.trim().toLowerCase() : '';
  const filtering = activeFilter !== 'all' || q !== '';
  const cards = document.querySelectorAll('.dest-card');
  cards.forEach((card, i)=>{
    const matchesRegion = activeFilter === 'all' || card.dataset.region === activeFilter;
    const matchesSearch = !q || card.dataset.name.includes(q) || card.dataset.tag.includes(q) || (card.dataset.text||'').includes(q) || card.dataset.region.toLowerCase().includes(q);
    // while browsing (no search/filter) show only the first `visibleCount`
    const collapsed = !filtering && i >= visibleCount;
    card.classList.toggle('hidden-card', !(matchesRegion && matchesSearch) || collapsed);
  });
  if(moreWrap){
    const remaining = Math.max(0, cards.length - visibleCount);
    moreWrap.style.display = (!filtering && cards.length > PAGE_SIZE) ? '' : 'none';
    if(moreBtn) moreBtn.innerHTML = remaining > 0
      ? 'See more places (' + remaining + ' left) <i class="fa-solid fa-chevron-down"></i>'
      : 'Show less <i class="fa-solid fa-chevron-up"></i>';
  }
}
if(moreBtn) moreBtn.addEventListener('click', ()=>{
  if(visibleCount < document.querySelectorAll('.dest-card').length){
    visibleCount += PAGE_SIZE;
    applyFilters();
  } else {
    visibleCount = PAGE_SIZE;
    applyFilters();
    document.getElementById('destinations').scrollIntoView({behavior:'smooth'});
  }
});
applyFilters();   // initial state: first 3 only
if(searchInput){
  searchInput.addEventListener('input', applyFilters);
  chips.forEach(chip=>{
    chip.addEventListener('click', ()=>{
      chips.forEach(c=>c.classList.remove('active'));
      chip.classList.add('active');
      activeFilter = chip.dataset.filter;
      applyFilters();
    });
  });
}

/* ===================== NAV SCROLL STATE ===================== */
const header = document.getElementById('siteHeader');
if(header){
  window.addEventListener('scroll', ()=>{
    header.classList.toggle('scrolled', window.scrollY > 40);
  });
}

/* ===================== MOBILE MENU ===================== */
const burgerBtn = document.getElementById('burgerBtn');
if(burgerBtn) burgerBtn.addEventListener('click', ()=>{
  const links = document.querySelector('.nav-links');
  const open = links.style.display === 'flex';
  links.style.display = open ? 'none' : 'flex';
  links.style.cssText += open ? '' : 'position:absolute;top:100%;left:0;right:0;background:var(--ink-2);flex-direction:column;padding:24px 32px;gap:18px;border-bottom:1px solid var(--line);';
});

/* ===================== BOOKING FORM — real submission to process_booking.php ===================== */
const bookingForm = document.getElementById('bookingForm');
if(bookingForm) bookingForm.addEventListener('submit', async function(e){
  e.preventDefault();
  const msgBox = document.getElementById('formMsg');
  const submitBtn = bookingForm.querySelector('button[type="submit"]');
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';

  const formData = new FormData(bookingForm);

  try{
    const res = await fetch('process_booking.php', { method: 'POST', body: formData });
    const raw  = await res.text();
    let data;
    try{ data = JSON.parse(raw); }
    catch(parseErr){
      throw new Error('Server did not return JSON — check that PHP is running and the database is set up (open setup.php).');
    }

    msgBox.classList.remove('ok','error');
    msgBox.classList.add('show', data.success ? 'ok' : 'error');
    msgBox.textContent = data.message;

    if(data.success){ bookingForm.reset(); }
  }catch(err){
    msgBox.classList.remove('ok');
    msgBox.classList.add('show','error');
    msgBox.textContent = err && err.message ? err.message : 'Something went wrong sending your enquiry. Please try again.';
  }finally{
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send Enquiry';
  }
});

/* ===================== SCROLL REVEAL ===================== */
const revealEls = document.querySelectorAll('.reveal');
const io = new IntersectionObserver((entries)=>{
  entries.forEach(en=>{ if(en.isIntersecting){ en.target.classList.add('in'); io.unobserve(en.target);} });
},{threshold:0.15});
revealEls.forEach(el=>io.observe(el));


/* ===================== HERO SEARCH BAR ===================== */
(function(){
  const form  = document.getElementById('heroSearch');
  if(!form) return;
  const where = document.getElementById('hsWhere');
  const when  = document.getElementById('hsWhen');
  const pax   = document.getElementById('hsPax');

  // "Select dates" placeholder → real date picker on focus
  when.addEventListener('focus', ()=>{
    when.type = 'date';
    when.min  = new Date().toISOString().slice(0,10);
    if(when.showPicker){ try{ when.showPicker(); }catch(_){} }
  });
  when.addEventListener('blur', ()=>{ if(!when.value) when.type = 'text'; });

  form.addEventListener('submit', (e)=>{
    e.preventDefault();
    const q = where.value.trim();

    // filter the destinations grid
    if(searchInput){
      searchInput.value = q;
      activeFilter = 'all';
      chips.forEach(c => c.classList.toggle('active', c.dataset.filter === 'all'));
      applyFilters();
    }
    // pre-fill the booking form so the visitor doesn't type it twice
    const fDate = document.getElementById('fDate');
    const fPax  = document.getElementById('fPax');
    const fMsg  = document.getElementById('fMsg');
    if(fDate && when.value) fDate.value = when.value;
    if(fPax) fPax.selectedIndex = pax.selectedIndex;
    if(fMsg && q && !fMsg.value) fMsg.value = "I'm interested in visiting " + q + ".";

    document.getElementById('destinations').scrollIntoView({behavior:'smooth'});
  });
})();

/* ===================== NAV: search icon, region dropdown, active link ===================== */
(function(){
  const sBtn = document.getElementById('navSearchBtn');
  if(sBtn) sBtn.addEventListener('click', ()=>{
    document.getElementById('destinations').scrollIntoView({behavior:'smooth'});
    setTimeout(()=>{ if(searchInput) searchInput.focus({preventScroll:true}); }, 600);
  });

  document.querySelectorAll('.dd a[data-region]').forEach(a=>{
    a.addEventListener('click', ()=>{
      const chip = document.querySelector('.chip[data-filter="' + a.dataset.region + '"]');
      if(chip) chip.click();
    });
  });

  const links = [...document.querySelectorAll('.nav-links > li > a[href^="#"]')];
  const ids = ['top','destinations','packages','guides','reviews','faq'];
  function spy(){
    let cur = 'top';
    ids.forEach(id=>{
      const el = document.getElementById(id);
      if(el && el.getBoundingClientRect().top <= 140) cur = id;
    });
    links.forEach(l => l.classList.toggle('active', l.getAttribute('href') === '#' + cur));
  }
  window.addEventListener('scroll', spy, {passive:true});
  spy();
})();
