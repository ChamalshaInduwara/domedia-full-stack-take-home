# Practical Test — Full Stack Developer (First Round)

Welcome, and thanks for your interest in the role. This is a short take-home
task. Please budget around **90 minutes to 2 hours**.

## A note on AI tools — please read this first

**You are allowed to use AI tools (ChatGPT, Claude, Copilot, etc.). We expect
it — that is how modern development works.**

However, this task is **not** about producing correct output. It is about
whether you *understand* the code you submit. Every part of this test asks you
to explain your work, and in the final step you will talk through your solution
on a short screen recording. You cannot pass by pasting code you don't
understand — so use AI as a tool, not as a substitute for understanding.

We would much rather see an honest, well-understood partial solution than a
perfect one you can't explain.

---

## The app

Inside the `app/` folder is a small **Workshop Registration** form built with
HTML, JavaScript (AJAX), PHP and MySQL. It is *supposed* to let a user register
for a workshop: they fill in the form, it submits via AJAX to `submit.php`,
which validates the input and inserts a row into a MySQL database.

**It does not work correctly.** We have planted several bugs across the front
end, the PHP, and the database logic. Some break the app visibly; others are
silent and only show up if you test carefully or read closely.

### Setup

1. Run `app/schema.sql` against your local MySQL to create the database and
   sample data.
2. Update the credentials in `app/db.php` if needed.
3. Serve the `app/` folder with PHP (for example: `php -S localhost:8000` from
   inside the `app/` folder) and open it in your browser.

---

## Part A — Find and fix the bugs

Get the form working: a valid submission should save a complete, correct row to
the `registrations` table and show a success message; an invalid submission
should show a helpful error and save nothing.

For **each bug you fix**, add a short note (1–2 sentences) to a file called
`FIXES.md`:

- What was wrong
- Why it caused the behaviour you saw
- Why your fix resolves it

> There are more than three bugs. We are more interested in your notes than in
> the raw count — a clearly explained fix is worth more than a silent one.

## Part B — Add one feature

Each workshop has a limited `capacity` (see the `workshops` table). Add a check
so that a registration is **rejected** if the requested number of seats would
push the workshop's total registered seats over its capacity. Show the user a
clear message when this happens. This should plug into the existing code — don't
rewrite the app from scratch.

## Part C — Explain your work

1. Answer these three questions in `FIXES.md`:

   a. One of the bugs you fixed was a **security** problem. Explain what an
   attacker could do with the original code, and why your fix prevents it.

   b. Your Part B capacity check probably reads the current seat count and then
   inserts. If two people register for the last available seat at the *same
   instant*, what can go wrong, and how would you prevent it?

   c. The original code could report "success" even when the database operation
   did not actually succeed. How would you make the code report a real failure?

2. Record a short screen recording (3–5 minutes, e.g. with Loom) where you walk
   us through your solution — show the app working, then talk through **two or
   three** of your fixes and *why* you made them. Share the link in `FIXES.md`.

---

## What to submit

Please include **everything we would need to run your project from scratch on a
fresh computer** — don't assume anything is already installed or configured:

- The fixed `app/` folder (all of your code files)
- `schema.sql` (so we can create the database and sample data exactly as you did)
- `FIXES.md` (your bug notes + the three answers + your recording link)
- A short note in `FIXES.md` of anything special needed to run it — for example,
  the PHP version you used, or which credentials to change in `db.php`

Zip it up and send it back to us.

> **About the next round:** if you're invited to our office, you'll work with
> *this same submission* on our computer — we set it up in advance from the files
> you send us, so you won't need to bring a laptop. Please make sure everything
> needed to run the project is included in what you submit.

Good luck — we're looking forward to seeing how you think.
