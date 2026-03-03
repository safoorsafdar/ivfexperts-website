# WhatsApp & Email Marketing — ivfexperts.pk
**Assessed:** 2026-03-03 | **Status:** 🔴 Not Started
**Primary channel:** WhatsApp (dominant in Pakistan) | **Secondary:** Email
**Goal:** Convert leads → consultations, retain past patients, drive referrals

---

## Executive Summary

In Pakistan, WhatsApp is the primary communication channel for healthcare — patients research on Google, but they contact doctors on WhatsApp. The site already has a WhatsApp CTA (`wa.me/923111101483`) but it lacks: pre-filled context, follow-up automation, a broadcast list for past patients, and any email capture or nurture strategy. Building a proper WhatsApp and email system turns every content investment (blog posts, tools, SEO) into a sustained pipeline rather than one-off traffic.

**Key insight:** Pakistani fertility patients make decisions over weeks or months. A patient who visits today and doesn't book may book in 3 months if they receive educational nurture content. Without a follow-up system, these potential patients are permanently lost.

---

## WHATSAPP BUSINESS SETUP

### ✅ Task 1: Upgrade to WhatsApp Business and configure the profile
- **Current state:** Likely using a personal WhatsApp number for clinic communication
- **Upgrade steps:**
  1. Download WhatsApp Business app (free) on the clinic's dedicated phone
  2. Set up the business profile:
     - **Business name:** IVF Experts Pakistan — Dr. Adnan Jabbar
     - **Category:** Medical & Health
     - **Description:** "Pakistan's leading fertility specialist. IVF, ICSI, PCOS, Male Infertility & more. Message us to book a consultation."
     - **Address:** Clinic address in Lahore
     - **Business hours:** Actual hours
     - **Website:** https://ivfexperts.pk
     - **Email:** clinic contact email
  3. Set an **Away Message** (for outside business hours):
     > "Thank you for contacting IVF Experts. We're currently closed but will respond first thing in the morning. For urgent queries, please leave a detailed message and we'll prioritize your response. Visit ivfexperts.pk for more information."
  4. Set a **Greeting Message** (sent to new contacts automatically):
     > "Welcome to IVF Experts Pakistan! 👋 I'm Dr. Adnan Jabbar's team. How can we help you today? You can ask about: IVF / ICSI, Male Infertility, PCOS, Teleconsultation, or Costs & Appointments."
  5. Set up **Quick Replies** (keyboard shortcuts for common responses):
     - `/fee` → Consultation fee and appointment booking info
     - `/tele` → Teleconsultation information for out-of-city patients
     - `/cost` → IVF cost range and link to cost estimator tool
     - `/location` → Clinic address + Google Maps link
- **Priority:** 🔴 Critical

### ✅ Task 2: Optimize WhatsApp CTAs on the website
- **Current:** Single `wa.me/923111101483` link with no pre-filled message
- **Fix:** Update all WhatsApp links to include context-specific pre-filled messages:
  ```php
  // In includes/: Create a helper function
  function whatsappLink($message = '') {
      $phone = '923111101483';
      $default = 'Hello Dr. Adnan, I would like to book a fertility consultation.';
      $text = urlencode($message ?: $default);
      return "https://wa.me/{$phone}?text={$text}";
  }
  ```
  **Per-page pre-filled messages:**
  - Homepage: `"Hello, I'd like to book a fertility consultation with Dr. Adnan."`
  - IVF page: `"Hello, I have questions about IVF treatment. Could we schedule a consultation?"`
  - Male infertility pages: `"Hello, I'd like to discuss male infertility treatment options with Dr. Adnan."`
  - Blog posts: `"Hello, I read your article about [topic] and would like to discuss my situation."`
  - After tool results: `"Hello, I just used the IVF Success Calculator and would like Dr. Adnan to review my results."`
- **Priority:** 🔴 High (see also [02-page-cro.md](02-page-cro.md) Task 12)

### ✅ Task 3: Build WhatsApp broadcast lists for past patients
- **What it is:** WhatsApp Business allows sending messages to up to 256 contacts at once (broadcast lists) — each recipient receives it as a 1:1 message, not a group message. Recipients must have your number saved.
- **Segments to build:**
  1. **Active patients** — currently in treatment
  2. **Past patients (successful)** — completed treatment, good outcome
  3. **Past patients (pending)** — consulted but haven't started treatment
  4. **Enquiry leads** — messaged but never booked
- **Broadcast content ideas:**
  - New blog post: "Dr. Adnan published a new article: [title] — [link]"
  - New free tool launch: "Check your IVF success chances with our free calculator: [link]"
  - Festival greetings (Eid, etc.) — humanizing touchpoint
  - Educational tips: "Did you know? Vitamin D deficiency affects 70% of Pakistani women and can impact fertility."
  - Review request: "We'd love your feedback — a Google review helps other couples find us: [link]"
- **Compliance:** Only message people who have consented (contacted the clinic first). Never purchase or scrape contact lists.
- **Priority:** 🔴 High

---

## LEAD NURTURE SEQUENCES

### ✅ Task 4: Design the consultation-to-booking WhatsApp sequence
- **Problem:** Many patients message but don't book. Without follow-up, these leads go cold.
- **Sequence for unresponsive leads (after initial message, no booking):**

  | Day | Message | Goal |
  |---|---|---|
  | Day 0 | Initial response to their query (immediate) | Answer their question |
  | Day 2 | Follow-up if no reply: "Did you have a chance to review our response? Happy to answer any more questions about [their topic]." | Re-engage |
  | Day 7 | Educational message related to their condition: "Many patients with [condition] find this article helpful: [link]" | Provide value |
  | Day 14 | Soft ask: "We're still here whenever you're ready to take the next step. Our consultation slots for [month] are filling up." | Create gentle urgency |
  | Day 30 | Final touch: "Just checking in — we're always available if you have questions about fertility treatment." | Stay top of mind |

- **Important:** All messages must feel personal, not automated. Use their name. Reference their specific condition if known.
- **Priority:** 🔴 High

### ✅ Task 5: Design post-consultation follow-up sequence
- **After a consultation (whether they proceed to treatment or not):**

  | Timing | Message | Goal |
  |---|---|---|
  | Same day | "Thank you for coming in today. Here is the summary of what we discussed: [summary]. Please don't hesitate to reach out with any questions." | Reinforce trust |
  | Day 3 | "How are you feeling after our consultation? Do you have any questions about the treatment plan we discussed?" | Check-in |
  | Day 7 | Share a relevant educational resource based on their diagnosis | Add value |
  | Day 14 | If not booked: "Whenever you're ready to start your treatment journey, our team is here." | Gentle persistence |
  | Day 30 | "A quick note — we've had [X] successful pregnancies this month. We'd love for you to be our next success story." | Social proof |

- **Priority:** 🟡 Medium

---

## EMAIL MARKETING SETUP

### ✅ Task 6: Set up email capture on the site
- **Current state:** No email capture exists — all leads go directly to WhatsApp or contact form. Blog readers, tool users, and educational page visitors leave with no opt-in.
- **Email capture points to add:**
  1. **Blog posts:** Exit-intent or inline email opt-in: "Get Dr. Adnan's monthly fertility newsletter — no spam, just evidence-based guidance."
  2. **Free tools:** After showing results: "Email me my results + a personalized guide" (captures email with consent)
  3. **Contact page:** Optional newsletter opt-in checkbox on the form
  4. **Footer:** "Subscribe to our fertility newsletter" — minimal friction, just email field
- **Email service provider (ESP) — choose one:**
  - **Mailchimp (free up to 500 contacts):** Easiest to start. Good for Pakistan.
  - **Brevo (free up to 300 emails/day):** Better deliverability than Mailchimp in some regions
  - **ConvertKit (paid, ~$29/mo):** Best for content-driven nurture sequences
- **Recommended:** Start with Mailchimp or Brevo. Migrate to ConvertKit if list exceeds 1000 subscribers.
- **Priority:** 🔴 High

### ✅ Task 7: Build the email welcome sequence (automated)
- **Trigger:** When someone subscribes via any email capture point
- **Welcome sequence (5 emails over 2 weeks):**

  | Email | Timing | Subject | Content |
  |---|---|---|---|
  | 1 | Immediate | "Welcome — here's what to expect from Dr. Adnan" | Who Dr. Adnan is, what the newsletter covers, top 3 resources |
  | 2 | Day 2 | "Understanding your fertility — where to start" | Link to most relevant pillar content based on how they subscribed |
  | 3 | Day 5 | "The question every fertility patient asks first" | Address the cost / success rate question head-on |
  | 4 | Day 9 | "A patient story that might sound familiar" | Anonymized patient journey — fear → hope → outcome |
  | 5 | Day 14 | "When you're ready, we're here" | Soft consultation CTA with WhatsApp link |

- **Priority:** 🔴 High (set up before publishing first blog post)

### ✅ Task 8: Set up monthly newsletter
- **Name:** "Fertility Forward by Dr. Adnan Jabbar" or "The IVF Experts Monthly"
- **Cadence:** First week of every month
- **Template structure (consistent each month):**
  ```
  1. Lead article: Latest blog post or original insight (200 words)
  2. Quick tip: One actionable fertility/lifestyle tip (50 words)
  3. Tool spotlight: Feature a free tool (when built)
  4. Patient story: Brief anonymized success note
  5. CTA: "Ready to book your consultation?" → WhatsApp link
  ```
- **Subject line formula:** "[Month] fertility insights from Dr. Adnan Jabbar — [specific topic]"
- **Priority:** 🟡 Medium (start after welcome sequence is live)

---

## PAST PATIENT REFERRAL PROGRAM

### ✅ Task 9: Create a simple referral program via WhatsApp
- **Goal:** Past patients are the best source of new patients in fertility care — trust and word of mouth are everything.
- **Structure (keep it simple):**
  - When a patient completes treatment with a positive outcome, send:
    > "We are so grateful you trusted us with your journey. If you know anyone struggling with fertility, please don't hesitate to pass on our number. Your referral means everything to our clinic and to the families we help."
  - No monetary incentive needed — emotional resonance is enough in this context
  - Optional: "Refer a friend and we'll provide them with a complimentary initial consultation (normally PKR X)"
- **Tracking:** Ask new patients "How did you hear about us?" — log referral sources
- **Priority:** 🟡 Medium

---

## WHATSAPP BROADCAST CALENDAR

### Monthly broadcast schedule:
| Week | Broadcast type | Content |
|---|---|---|
| Week 1 | Educational | New blog post or research insight |
| Week 2 | Social proof | Patient outcome story (anonymized) |
| Week 3 | Resource | Free tool or guide launch/spotlight |
| Week 4 | Community | Festival greeting / awareness month tie-in |

**Awareness months to use:**
- June: Men's Health Month (male infertility content)
- September: PCOS Awareness Month
- October: World Infertility Awareness Month
- November: Prematurity Awareness Month

---

## Technical Implementation

### ✅ Task 10: Add newsletter subscription table to database
- **When:** When email capture forms are added to the site
- **DB table:** `newsletter_subscribers`
  ```sql
  CREATE TABLE newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    first_name VARCHAR(100),
    source VARCHAR(100),     -- 'blog', 'tool_ivf_calculator', 'contact_form', 'footer'
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    unsubscribed_at TIMESTAMP NULL
  );
  ```
- **On form submit:** Insert to DB + add to Mailchimp/Brevo via API
- **Unsubscribe:** Every email must include an unsubscribe link (legal requirement — Pakistan Electronic Crimes Act and international email law)
- **Priority:** 🟡 Medium (when email forms are built)

### ✅ Task 11: Set up GTM tracking for email and WhatsApp engagement
- **Events to track:**
  - `newsletter_signup` — when subscribe form is submitted (dataLayer push)
  - `email_cta_click` — clicks on links from email campaigns (use UTM parameters on all email links)
  - UTM template for emails: `?utm_source=newsletter&utm_medium=email&utm_campaign=2026-03-monthly`
  - UTM template for WhatsApp broadcasts: `?utm_source=whatsapp&utm_medium=broadcast&utm_campaign=march-blog`
- **In GA4:** Create a channel group that includes `whatsapp` and `email` as separate acquisition sources
- **Priority:** 🟡 Medium

---

## Metrics to Track
- WhatsApp: Response rate within 1 hour (target: 90%+)
- WhatsApp: Consultation booking rate from initial contact (target: 30%+)
- Email: List size growth (monthly)
- Email: Open rate (target: 35%+ for medical)
- Email: Click rate (target: 5%+ for medical)
- Referral rate: % of new patients referred by past patients (track via "how did you hear about us?")
- Lead-to-booking conversion rate overall (consults booked ÷ total inquiries)
