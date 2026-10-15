<?php
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
ensure_extra_destinations($db);
$destRows = $db->query("SELECT * FROM destinations WHERE is_active = 1 ORDER BY sort_order ASC, name ASC")->fetchAll();
$pkgRows  = $db->query("SELECT * FROM packages WHERE is_active = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();

/** Photo path for a destination: DB value first, else a file named after it in assets/images/destinations/ */
function destination_image(array $d): ?string
{
    if (!empty($d['image'])) { return $d['image']; }
    $slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(str_replace(["'", '’'], '', $d['name']))), '-');
    $candidates = [$slug, strtok($slug, '-')];   // 'galle-fort' then 'galle'; 'yala-national-park' then 'yala'
    foreach ($candidates as $c) {
        foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
            $rel = "assets/images/destinations/$c.$ext";
            if (is_file(__DIR__ . '/' . $rel)) { return $rel; }
        }
    }
    return null;
}

$destinationsForJs = array_map(function ($d) {
    return [
        'name' => $d['name'], 'region' => $d['region'], 'tag' => $d['tag'],
        'desc' => $d['short_desc'], 'longDesc' => $d['long_desc'],
        'bestTime' => $d['best_time'], 'duration' => $d['duration'], 'highlight' => $d['highlight'],
        'icon' => $d['icon'], 'grad' => $d['gradient'], 'image' => destination_image($d),
    ];
}, $destRows);

$packagesForJs = array_map(function ($p) {
    return [
        'name' => $p['name'], 'days' => $p['days_label'], 'price' => (float)$p['price'],
        'desc' => $p['description'], 'items' => json_decode($p['items_json'], true) ?: [],
    ];
}, $pkgRows);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hidden Ceylon — Discover Sri Lanka</title>
<meta name="description" content="Hidden Ceylon — a travel and tourism guide to Sri Lanka's ancient cities, hill country, coastline and wildlife.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;1,500;1,600;1,700&family=Poppins:wght@300;400;500;600;700&family=Kaushan+Script&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="icon" type="image/png" href="assets/images/favicon.png">
<link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
</head>
<body>


<header id="siteHeader">
  <nav class="nav wrap">
    <a href="#top" class="brand" aria-label="Hidden Ceylon — home"><img src="assets/images/logo.png?v=3" width="157" height="62" alt="Hidden Ceylon — Discover, Explore, Experience"></a>
    <ul class="nav-links">
      <li><a href="#top" class="active">Home</a></li>
      <li class="has-dd">
        <a href="#destinations">Destinations <i class="fa-solid fa-chevron-down"></i></a>
        <ul class="dd">
          <li><a href="#destinations" data-region="all">All destinations</a></li>
          <li><a href="#destinations" data-region="Cultural Triangle">Cultural Triangle</a></li>
          <li><a href="#destinations" data-region="Hill Country">Hill Country</a></li>
          <li><a href="#destinations" data-region="South Coast">South Coast</a></li>
          <li><a href="#destinations" data-region="Wildlife">Wildlife</a></li>
        </ul>
      </li>
      <li><a href="#packages">Packages</a></li>
      <li><a href="#guides">Travel Guides</a></li>
      <li><a href="#reviews">Reviews</a></li>
      <li><a href="#faq">FAQ</a></li>
    </ul>
    <div class="nav-actions">
      <button class="icon-btn" id="navSearchBtn" aria-label="Search destinations"><i class="fa-solid fa-magnifying-glass"></i></button>
      <a href="#booking" class="btn btn-gold nav-cta"><i class="fa-solid fa-paper-plane"></i><span>Plan My Trip</span></a>
      <a href="admin/login.php" class="icon-btn nav-user" aria-label="Admin login"><i class="fa-regular fa-user"></i></a>
      <button class="burger" id="burgerBtn" aria-label="Menu"><span></span><span></span><span></span></button>
    </div>
  </nav>
</header>

<!-- ============ HERO ============ -->
<section class="hero has-photo" id="top">
  <div class="hero-sky"></div>
  <div class="hero-photo"></div>

  <div class="hero-content">
    <div class="wrap">
      <div class="eyebrow">Sri Lanka Travel &amp; Tourism</div>
      <h1>The island that hides<br><em>a thousand mornings.</em></h1>
      <p class="hero-sub">From the citadel at Sigiriya to the tea ridges of Ella and the reefs off Mirissa — plan a Sri Lanka trip that actually feels like Sri Lanka.</p>

      <form class="hero-search" id="heroSearch" autocomplete="off">
        <label class="hs-field">
          <i class="fa-solid fa-location-dot hs-icon"></i>
          <span class="hs-body">
            <span class="hs-label">Where to?</span>
            <input type="text" id="hsWhere" list="heroDestList" placeholder="e.g. Ella, Sigiriya, Mirissa">
          </span>
          <i class="fa-solid fa-chevron-down hs-chev"></i>
        </label>
        <label class="hs-field">
          <i class="fa-regular fa-calendar hs-icon"></i>
          <span class="hs-body">
            <span class="hs-label">When?</span>
            <input type="text" id="hsWhen" placeholder="Select dates">
          </span>
          <i class="fa-solid fa-chevron-down hs-chev"></i>
        </label>
        <label class="hs-field">
          <i class="fa-solid fa-user-group hs-icon"></i>
          <span class="hs-body">
            <span class="hs-label">Travelers</span>
            <select id="hsPax">
              <option>1 Adult</option><option selected>2 Adults</option><option>3–5 Travelers</option><option>6+ Travelers</option>
            </select>
          </span>
          <i class="fa-solid fa-chevron-down hs-chev"></i>
        </label>
        <button type="submit" class="btn btn-gold hs-btn"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        <datalist id="heroDestList">
          <?php foreach ($destRows as $d): ?><option value="<?= e($d['name']) ?>"><?php endforeach; ?>
        </datalist>
      </form>
    </div>
  </div>

  <div class="hero-tagline" aria-hidden="true">
    More Than Just<br>a <em>Destination</em>
    <svg viewBox="0 0 160 14" fill="none"><path d="M2 10 C 40 2, 100 2, 158 8" stroke="#3FA9E8" stroke-width="3" stroke-linecap="round"/></svg>
  </div>

  <div class="wrap hero-features-wrap">
    <div class="hero-features">
      <div class="hf"><span class="hf-icon"><i class="fa-solid fa-mountain-sun"></i></span><span><strong>Stunning Landscapes</strong><small>Mountains, beaches, nature</small></span></div>
      <div class="hf"><span class="hf-icon"><i class="fa-solid fa-landmark"></i></span><span><strong>Rich Heritage</strong><small>Ancient cities &amp; culture</small></span></div>
      <div class="hf"><span class="hf-icon"><i class="fa-solid fa-leaf"></i></span><span><strong>Unique Experiences</strong><small>Tea, wildlife, adventure</small></span></div>
      <div class="hf"><span class="hf-icon"><i class="fa-regular fa-heart"></i></span><span><strong>Local Insights</strong><small>Real stories, real people</small></span></div>
    </div>
  </div>
</section>

<!-- ============ MARQUEE ============ -->
<div class="strip">
  <div class="strip-track" id="marquee">
    <span>★ Sigiriya Rock Fortress</span><span>★ Ella Nine Arch Bridge</span><span>★ Yala National Park</span><span>★ Galle Fort</span><span>★ Mirissa Whale Watching</span><span>★ Nuwara Eliya Tea Country</span><span>★ Anuradhapura Sacred City</span><span>★ Trincomalee Beaches</span>
  </div>
</div>

<!-- ============ DESTINATIONS ============ -->
<section class="section" id="destinations">
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow">Where to Go</div>
      <h2>Places worth building a trip around</h2>
      <p>Search by name or filter by region — the cultural triangle, the hill country, the south coast, and the wild east.</p>
    </div>

    <div class="search-panel reveal">
      <div class="field">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="destSearch" placeholder="Search a destination — try 'Ella' or 'beach'">
      </div>
      <div class="chip-group" id="chipGroup">
        <button class="chip active" data-filter="all">All</button>
        <button class="chip" data-filter="Cultural Triangle">Cultural Triangle</button>
        <button class="chip" data-filter="Hill Country">Hill Country</button>
        <button class="chip" data-filter="South Coast">South Coast</button>
        <button class="chip" data-filter="Wildlife">Wildlife</button>
      </div>
    </div>

    <div class="dest-grid" id="destGrid">
      <!-- cards injected by JS -->
    </div>
    <div class="dest-more" id="destMore" style="display:none">
      <button type="button" class="btn btn-ghost" id="destMoreBtn">See more places</button>
    </div>
  </div>
</section>

<!-- ============ PACKAGES ============ -->
<section class="section alt" id="packages">
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow">Tour Packages</div>
      <h2>Pre-built itineraries, or a starting point for your own</h2>
      <p>Every package includes a private driver, hand-picked stays, and a local guide for cultural sites.</p>
    </div>
    <div class="pkg-scroller" id="pkgScroller">
      <!-- cards injected by JS -->
    </div>
  </div>
</section>

<!-- ============ TRAVEL GUIDES ============ -->
<section class="section" id="guides">
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow">Travel Guides</div>
      <h2>Plan it right the first time</h2>
      <p>Practical notes from people who've actually done the Kandy–Ella train at 5am.</p>
    </div>
    <div class="guide-grid" id="guideGrid"></div>
  </div>
</section>

<!-- ============ REVIEWS ============ -->
<section class="section alt" id="reviews">
  <div class="wrap">
    <div class="section-head reveal" style="margin-left:auto; margin-right:auto; text-align:center;">
      <div class="eyebrow" style="justify-content:center;">Traveler Reviews</div>
      <h2>What it's actually like</h2>
    </div>
    <div class="reviews-wrap" id="reviewsWrap"></div>
  </div>
</section>

<!-- ============ FAQ ============ -->
<section class="section" id="faq">
  <div class="wrap" style="max-width:820px;">
    <div class="section-head reveal">
      <div class="eyebrow">FAQ</div>
      <h2>Before you ask</h2>
    </div>
    <div id="faqList"></div>
  </div>
</section>

<!-- ============ BOOKING / CONTACT ============ -->
<section class="section alt" id="booking">
  <div class="wrap">
    <div class="section-head reveal">
      <div class="eyebrow">Get in Touch</div>
      <h2>Start planning your Sri Lanka trip</h2>
    </div>
    <div class="booking reveal">
      <div class="booking-side">
        <h3>Hidden Ceylon Travels</h3>
        <p>Tell us your dates and interests — we'll reply within one business day with a draft itinerary and quote.</p>
        <div class="contact-line"><i class="fa-solid fa-location-dot"></i> 42 Galle Road, Colombo 03, Sri Lanka</div>
        <div class="contact-line"><i class="fa-solid fa-phone"></i> +94 11 234 5678</div>
        <div class="contact-line"><i class="fa-solid fa-envelope"></i> hello@hiddenceylon.lk</div>
        <div class="contact-line"><i class="fa-solid fa-clock"></i> Mon – Sat, 9:00 AM – 6:00 PM (GMT+5:30)</div>
        <div class="social-row">
          <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
          <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
          <a href="#" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
          <a href="#" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
        </div>
      </div>
      <form class="booking-form" id="bookingForm">
        <?= csrf_field() ?>
        <div class="form-row">
          <div class="form-group"><label for="fName">Full Name</label><input type="text" id="fName" name="fullName" required></div>
          <div class="form-group"><label for="fEmail">Email</label><input type="email" id="fEmail" name="email" required></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label for="fDate">Travel Date</label><input type="date" id="fDate" name="travelDate" required></div>
          <div class="form-group"><label for="fPax">Travelers</label>
            <select id="fPax" name="travelers"><option>1 traveler</option><option selected>2 travelers</option><option>3–5 travelers</option><option>6+ travelers</option></select>
          </div>
        </div>
        <div class="form-group"><label for="fPkg">Interested Package</label>
          <select id="fPkg" name="packageInterest">
            <option>Not sure yet — advise me</option>
            <?php foreach ($pkgRows as $p): ?>
              <option><?= e($p['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label for="fMsg">Message</label><textarea id="fMsg" name="message" placeholder="Anything specific — honeymoon, family trip, hiking, food tour..."></textarea></div>
        <div class="form-msg" id="formMsg"></div>
        <button type="submit" class="btn btn-gold" style="align-self:flex-start;"><i class="fa-solid fa-paper-plane"></i> Send Enquiry</button>
      </form>
    </div>
  </div>
</section>

<!-- ============ FOOTER ============ -->
<footer>
  <div class="wrap">
    <div class="footer-top">
      <div class="footer-brand">
        <a href="#top" class="footer-logo" aria-label="Hidden Ceylon — home"><img src="assets/images/logo.png?v=3" width="212" height="84" alt="Hidden Ceylon"></a>
        <p>An independently run guide to traveling Sri Lanka well — real routes, honest pricing, no filler.</p>
      </div>
      <div>
        <h5>Explore</h5>
        <ul><li><a href="#destinations">Destinations</a></li><li><a href="#packages">Packages</a></li><li><a href="#guides">Travel Guides</a></li></ul>
      </div>
      <div>
        <h5>Company</h5>
        <ul><li><a href="#reviews">Reviews</a></li><li><a href="#faq">FAQ</a></li><li><a href="#booking">Contact</a></li></ul>
      </div>
      <div>
        <h5>Regions</h5>
        <ul><li>Cultural Triangle</li><li>Hill Country</li><li>South Coast</li><li>Wildlife &amp; East</li></ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© 2026 Hidden Ceylon Travels. All rights reserved.</span>
      <span>Built for showcasing Sri Lanka, responsibly.</span>
    </div>
  </div>
</footer>

<!-- ============ DESTINATION DETAIL MODAL ============ -->
<div class="modal-overlay" id="destModal">
  <div class="modal-box">
    <div class="modal-photo">
      <button class="modal-close" id="modalCloseBtn" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
      <div class="icon-mark modal-icon"></div>
    </div>
    <div class="modal-body">
      <div class="region modal-region"></div>
      <h3 class="modal-title"></h3>
      <div class="modal-tag"></div>
      <p class="modal-desc"></p>
      <div class="modal-facts">
        <div class="modal-fact"><i class="fa-solid fa-calendar"></i><span><strong>Best time:</strong> <span class="fact-time"></span></span></div>
        <div class="modal-fact"><i class="fa-solid fa-clock"></i><span><strong>Suggested stay:</strong> <span class="fact-duration"></span></span></div>
        <div class="modal-fact"><i class="fa-solid fa-star"></i><span><strong>Highlight:</strong> <span class="fact-highlight"></span></span></div>
        <div class="modal-fact"><i class="fa-solid fa-map-pin"></i><span><strong>Region:</strong> <span class="fact-region"></span></span></div>
      </div>
      <div class="modal-actions">
        <a href="#booking" class="btn btn-gold" onclick="closeDestModal()"><i class="fa-solid fa-paper-plane"></i> Enquire About This Trip</a>
        <button class="btn btn-ghost" onclick="closeDestModal()">Close</button>
      </div>
    </div>
  </div>
</div>


<script>
  window.HC_DESTINATIONS = <?= json_encode($destinationsForJs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS) ?>;
  window.HC_PACKAGES = <?= json_encode($packagesForJs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS) ?>;
</script>
<script src="assets/js/main.js?v=<?= filemtime(__DIR__ . '/assets/js/main.js') ?>"></script>
</body>
</html>
