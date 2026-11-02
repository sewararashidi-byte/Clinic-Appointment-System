
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Clinic Appointment System brings doctor discovery, appointment booking, scheduling, and clinic management together in one organized platform."
    >

    <title>
        Clinic Appointment System | Care, Better Connected
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>

        :root {
            --navy: #102d46;
            --navy-deep: #0a2235;
            --navy-light: #173e56;

            --teal: #087d85;
            --teal-dark: #08666e;
            --mint: #a8eadc;

            --ink: #16364b;
            --muted: #6c8291;
            --border: #dfe9ec;

            --background: #f5f9fa;
            --white: #ffffff;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            background: var(--background);
            color: var(--ink);

            font-family:
                "DM Sans",
                "Segoe UI",
                sans-serif;

            -webkit-font-smoothing: antialiased;
        }

        button {
            font: inherit;
            cursor: pointer;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        a:focus-visible,
        button:focus-visible {
            outline: 3px solid #65cfc4;
            outline-offset: 4px;
        }

        svg {
            display: block;
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .container {
            width: min(1220px, calc(100% - 72px));
            margin-inline: auto;
        }

        .skip-link {
            position: absolute;
            top: -70px;
            left: 20px;
            z-index: 100;

            padding: 12px 17px;
            border-radius: 8px;
            background: white;
            color: var(--navy);
            font-weight: 800;
        }

        .skip-link:focus {
            top: 15px;
        }

        /* Header */

        .site-header {
            position: relative;
            z-index: 20;

            background: var(--navy-deep);
            border-bottom: 1px solid rgba(255,255,255,.1);
        }

        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 25px;

            min-height: 90px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 13px;

            color: white;
        }

        .brand-mark {
            display: grid;
            place-items: center;

            width: 44px;
            height: 44px;

            border: 1px solid rgba(153,234,221,.28);
            border-radius: 13px;

            background: rgba(137,224,211,.1);
            color: #a8eadc;
        }

        .brand-mark svg {
            width: 25px;
            height: 25px;
        }

        .brand-text {
            font-family: "Manrope",sans-serif;
            font-size: .91rem;
            font-weight: 800;
            line-height: 1.4;
            letter-spacing: -.035em;
        }

        .desktop-nav {
            display: flex;
            align-items: center;
            gap: 33px;
        }

        .nav-link {
            color: #c1d4da;
            font-size: .87rem;
            font-weight: 700;
            transition: color .2s;
        }

        .nav-link:hover {
            color: white;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sign-in-link {
            color: #e0ecee;
            font-size: .86rem;
            font-weight: 700;
            padding: 12px 8px;
        }

        .sign-in-link:hover {
            color: white;
        }

        .header-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;

            min-height: 44px;
            padding: 12px 17px;

            border-radius: 10px;
            background: #b3efe2;
            color: #0b454d;

            font-size: .84rem;
            font-weight: 800;

            transition: background .2s, transform .2s;
        }

        .header-cta:hover {
            background: #d1f8ef;
            transform: translateY(-1px);
        }

        .header-cta svg {
            width: 16px;
            height: 16px;
        }

        .menu-toggle {
            display: none;
            align-items: center;
            justify-content: center;

            width: 44px;
            height: 44px;

            border: 1px solid rgba(255,255,255,.19);
            border-radius: 10px;

            background: rgba(255,255,255,.08);
            color: white;
        }

        .mobile-nav {
            display: none;
        }

        /* Hero */

        .hero {
            position: relative;
            isolation: isolate;
            overflow: hidden;

            color: white;

            background:
                radial-gradient(
                    ellipse at 90% 20%,
                    rgba(52,169,157,.22),
                    transparent 45%
                ),
                radial-gradient(
                    ellipse at 5% 80%,
                    rgba(43,105,139,.15),
                    transparent 42%
                ),
                linear-gradient(
                    125deg,
                    #0a2235 0%,
                    #12344a 57%,
                    #0b454e 100%
                );
        }

        .hero::before {
            content: "";
            position: absolute;
            z-index: -1;

            width: 700px;
            height: 700px;

            right: -330px;
            bottom: -540px;

            border: 1px solid rgba(163,232,224,.17);
            border-radius: 50%;

            box-shadow:
                0 0 0 85px rgba(163,232,224,.025),
                0 0 0 175px rgba(163,232,224,.018);

            pointer-events: none;
        }

        .hero-inner {
            display: grid;
            grid-template-columns: minmax(0,1fr) minmax(0,.9fr);
            align-items: center;
            gap: 72px;

            min-height: 680px;
            padding-top: 85px;
            padding-bottom: 95px;
        }

        .hero-copy {
            max-width: 620px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 12px;

            color: #afe9df;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .17em;
            text-transform: uppercase;
        }

        .eyebrow-line {
            width: 26px;
            height: 1px;
            background: #a9e5da;
        }

        .hero-title {
            margin: 29px 0 25px;

            font-family: "Manrope",sans-serif;
            font-size: clamp(3rem,5.1vw,5.05rem);
            font-weight: 800;
            letter-spacing: -.075em;
            line-height: 1.13;
        }

        .hero-title span {
            color: #aceadd;
        }

        .hero-description {
            max-width: 550px;
            margin: 0;

            color: #c4dce2;
            font-size: 1.02rem;
            line-height: 1.9;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 13px;

            margin-top: 34px;
        }

        .primary-button,
        .outline-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;

            min-height: 55px;
            padding: 15px 23px;

            border-radius: 12px;

            font-size: .9rem;
            font-weight: 800;

            transition:
                background .2s,
                transform .2s,
                box-shadow .2s;
        }

        .primary-button {
            background: #b4eee1;
            color: #0a424a;

            box-shadow: 0 12px 30px rgba(0,0,0,.09);
        }

        .primary-button:hover {
            background: #d0faf0;
            transform: translateY(-2px);
        }

        .outline-button {
            border: 1px solid rgba(230,250,246,.35);
            background: rgba(255,255,255,.045);
            color: white;
        }

        .outline-button:hover {
            background: rgba(255,255,255,.13);
        }

        .primary-button svg,
        .outline-button svg {
            width: 18px;
            height: 18px;
        }

        .hero-caption {
            margin-top: 32px;
            color: #91b4c0;
            font-size: .82rem;
            line-height: 1.7;
        }

        /* Appointment journey illustration */

        .journey-visual {
            position: relative;
            width: 100%;
            max-width: 495px;
            justify-self: end;
        }

        .journey-visual::before {
            content: "";
            position: absolute;

            inset: 40px -25px -30px 35px;

            border: 1px solid rgba(172,234,221,.13);
            border-radius: 30px;

            transform: rotate(5deg);
            pointer-events: none;
        }

        .journey-window {
            position: relative;
            overflow: hidden;

            border: 1px solid rgba(220,249,243,.22);
            border-radius: 23px;

            background: #f9fcfc;
            color: var(--ink);

            box-shadow: 0 30px 80px rgba(0,12,26,.23);
        }

        .window-top {
            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 20px 25px;

            border-bottom: 1px solid #e5edef;
            background: white;
        }

        .window-title {
            display: flex;
            align-items: center;
            gap: 11px;

            font-family: "Manrope",sans-serif;
            font-size: .83rem;
            font-weight: 800;
        }

        .window-icon {
            display: grid;
            place-items: center;

            width: 34px;
            height: 34px;

            border-radius: 10px;
            background: #e6f5f1;
            color: var(--teal);
        }

        .window-icon svg {
            width: 18px;
            height: 18px;
        }

        .window-dots {
            display: flex;
            gap: 5px;
        }

        .window-dots span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #d5e3e6;
        }

        .journey-content {
            padding: 30px;
        }

        .journey-kicker {
            display: block;

            color: var(--teal);
            font-size: .66rem;
            font-weight: 800;
            letter-spacing: .15em;
            text-transform: uppercase;
        }

        .journey-title {
            margin: 10px 0 9px;

            font-family: "Manrope",sans-serif;
            font-size: 1.38rem;
            font-weight: 800;
            letter-spacing: -.05em;
        }

        .journey-description {
            margin: 0 0 28px;
            color: #8396a2;
            font-size: .83rem;
            line-height: 1.7;
        }

        .journey-step {
            display: flex;
            align-items: flex-start;
            gap: 16px;

            padding: 19px 0;
            border-bottom: 1px solid #e5ecee;
        }

        .journey-step:first-of-type {
            padding-top: 0;
        }

        .journey-step:last-of-type {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .step-icon {
            display: grid;
            place-items: center;

            width: 43px;
            height: 43px;
            flex-shrink: 0;

            border-radius: 12px;
            background: #e6f5f1;
            color: #087d85;
        }

        .step-icon.blue {
            background: #e9f2fa;
            color: #46749c;
        }

        .step-icon.purple {
            background: #f0edfa;
            color: #7765a4;
        }

        .step-icon svg {
            width: 21px;
            height: 21px;
        }

        .step-text {
            flex: 1;
            min-width: 0;
        }

        .step-number {
            display: block;
            margin-bottom: 5px;

            color: #94a6b0;
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .11em;
        }

        .step-text strong {
            display: block;
            margin-bottom: 5px;

            font-family: "Manrope",sans-serif;
            font-size: .9rem;
            font-weight: 800;
        }

        .step-text p {
            margin: 0;

            color: #8396a2;
            font-size: .79rem;
            line-height: 1.65;
        }

        .window-footer {
            display: flex;
            align-items: center;
            gap: 10px;

            padding: 17px 25px;

            border-top: 1px solid #dcebe8;
            background: #eaf7f4;

            color: #247278;
            font-size: .78rem;
            font-weight: 700;
        }

        .window-footer svg {
            width: 17px;
            height: 17px;
        }

        /* Introductory section */

        .section {
            padding: 108px 0;
        }

        .section-heading {
            max-width: 690px;
            margin-bottom: 42px;
        }

        .section-kicker {
            display: block;
            margin-bottom: 15px;

            color: var(--teal);
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .17em;
            text-transform: uppercase;
        }

        .section-title {
            margin: 0;

            font-family: "Manrope",sans-serif;
            font-size: clamp(2rem,3.2vw,3.1rem);
            font-weight: 800;
            letter-spacing: -.065em;
            line-height: 1.23;
        }

        .section-description {
            margin: 18px 0 0;

            color: var(--muted);
            font-size: 1rem;
            line-height: 1.85;
        }

        /* Feature cards */

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3,minmax(0,1fr));
            gap: 21px;
        }

        .feature-card {
            min-width: 0;
            padding: 32px 30px;

            border: 1px solid var(--border);
            border-radius: 21px;

            background: white;
            box-shadow: 0 13px 37px rgba(21,66,82,.04);

            transition:
                transform .2s,
                border-color .2s,
                box-shadow .2s;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            border-color: #bdddd8;
            box-shadow: 0 19px 43px rgba(21,66,82,.075);
        }

        .feature-icon {
            display: grid;
            place-items: center;

            width: 58px;
            height: 58px;

            margin-bottom: 28px;

            border-radius: 17px;
            background: #e6f5f1;
            color: var(--teal);
        }

        .feature-icon.blue {
            background: #eaf2fa;
            color: #386b98;
        }

        .feature-icon.purple {
            background: #f0edfa;
            color: #7964aa;
        }

        .feature-icon svg {
            width: 28px;
            height: 28px;
        }

        .feature-card h3 {
            margin: 0 0 13px;

            font-family: "Manrope",sans-serif;
            font-size: 1.08rem;
            font-weight: 800;
            letter-spacing: -.045em;
        }

        .feature-card p {
            margin: 0;

            color: #718697;
            font-size: .89rem;
            line-height: 1.85;
        }

        /* How it works */

        .process-section {
            padding: 100px 0;

            background:
                radial-gradient(
                    ellipse at 98% 15%,
                    rgba(34,153,144,.055),
                    transparent 40%
                ),
                #eaf3f3;

            border-top: 1px solid #dfeaea;
            border-bottom: 1px solid #dfeaea;
        }

        .process-grid {
            display: grid;
            grid-template-columns: repeat(3,minmax(0,1fr));
            gap: 30px;
        }

        .process-item {
            padding: 7px 0;
        }

        .process-number {
            display: block;
            margin-bottom: 27px;

            font-family: "Manrope",sans-serif;
            font-size: 3.4rem;
            font-weight: 800;
            letter-spacing: -.09em;

            color: #aacdc9;
            line-height: 1;
        }

        .process-rule {
            height: 2px;
            margin-bottom: 27px;

            background: linear-gradient(
                90deg,
                #087d85 0,
                #b8d6d3 45%,
                #d4e6e4 100%
            );
        }

        .process-item h3 {
            margin: 0 0 13px;

            font-family: "Manrope",sans-serif;
            font-size: 1.06rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .process-item p {
            margin: 0;
            color: #6c8491;
            font-size: .9rem;
            line-height: 1.8;
        }

        /* Patient and clinic team entry */

        .access-section {
            padding: 110px 0;
        }

        .access-grid {
            display: grid;
            grid-template-columns: repeat(2,minmax(0,1fr));
            gap: 23px;
        }

        .access-card {
            display: flex;
            flex-direction: column;
            align-items: flex-start;

            min-height: 335px;
            padding: 38px;

            border-radius: 23px;
            overflow: hidden;
        }

        .access-card.patient {
            background: #e9f6f3;
            border: 1px solid #d3e9e3;
        }

        .access-card.team {
            background: #eaf1f7;
            border: 1px solid #d8e5ee;
        }

        .access-icon {
            display: grid;
            place-items: center;

            width: 52px;
            height: 52px;

            margin-bottom: 25px;

            border-radius: 14px;
            background: white;
            color: var(--teal);
        }

        .access-card.team .access-icon {
            color: #3e7194;
        }

        .access-icon svg {
            width: 25px;
            height: 25px;
        }

        .access-label {
            display: block;
            margin-bottom: 12px;

            color: var(--teal);
            font-size: .7rem;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .access-card.team .access-label {
            color: #42718f;
        }

        .access-card h3 {
            margin: 0 0 14px;

            font-family: "Manrope",sans-serif;
            font-size: clamp(1.45rem,2.5vw,1.9rem);
            font-weight: 800;
            letter-spacing: -.055em;
        }

        .access-card p {
            max-width: 430px;
            margin: 0 0 25px;

            color: #667f8c;
            font-size: .89rem;
            line-height: 1.8;
        }

        .access-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;

            margin-top: auto;
            color: var(--teal);

            font-size: .86rem;
            font-weight: 800;
        }

        .access-card.team .access-link {
            color: #376987;
        }

        .access-link:hover {
            text-decoration: underline;
            text-underline-offset: 5px;
        }

        .access-link svg {
            width: 17px;
            height: 17px;
        }

        /* Bottom call to action */

        .bottom-cta {
            position: relative;
            overflow: hidden;

            margin-bottom: 105px;
            padding: 65px 60px;

            border-radius: 28px;
            background:
                radial-gradient(
                    ellipse at 100% 0%,
                    rgba(72,180,170,.23),
                    transparent 48%
                ),
                linear-gradient(
                    118deg,
                    #102d46,
                    #0e4652
                );

            color: white;
        }

        .bottom-cta::after {
            content: "";
            position: absolute;

            width: 330px;
            height: 330px;

            right: -160px;
            bottom: -245px;

            border: 1px solid rgba(168,234,220,.25);
            border-radius: 50%;

            pointer-events: none;
        }

        .bottom-cta h2 {
            max-width: 750px;
            margin: 0 0 19px;

            font-family: "Manrope",sans-serif;
            font-size: clamp(2rem,3.5vw,3.3rem);
            font-weight: 800;
            letter-spacing: -.067em;
            line-height: 1.25;
        }

        .bottom-cta p {
            max-width: 650px;
            margin: 0 0 27px;

            color: #c4dde2;
            font-size: .96rem;
            line-height: 1.85;
        }

        /* Footer */

        .site-footer {
            padding: 38px 0 28px;

            background: #0a2235;
            color: #aac0ca;
        }

        .footer-main {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 27px;

            padding-bottom: 33px;
            border-bottom: 1px solid rgba(255,255,255,.12);
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 13px;
            color: white;

            font-family: "Manrope",sans-serif;
            font-size: .86rem;
            font-weight: 800;
            line-height: 1.5;
        }

        .footer-description {
            max-width: 380px;
            margin: 16px 0 0;

            color: #8ea9b7;
            font-size: .84rem;
            line-height: 1.8;
        }

        .footer-links {
            display: flex;
            flex-wrap: wrap;
            gap: 20px 28px;

            padding-top: 12px;
        }

        .footer-links a {
            font-size: .85rem;
            font-weight: 700;
        }

        .footer-links a:hover {
            color: white;
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;

            padding-top: 25px;

            color: #819ca9;
            font-size: .77rem;
        }

        /* Responsive */

        @media (max-width: 1100px) {

            .hero-inner {
                gap: 38px;
            }

            .hero-title {
                font-size: clamp(2.8rem,5vw,4.4rem);
            }

            .journey-content {
                padding: 23px;
            }

            .feature-card {
                padding: 28px 25px;
            }

        }

        @media (max-width: 900px) {

            .desktop-nav,
            .header-actions {
                display: none;
            }

            .menu-toggle {
                display: inline-flex;
            }

            .mobile-nav.is-open {
                display: flex;
                flex-direction: column;
                gap: 7px;

                padding: 10px 0 23px;
            }

            .mobile-nav a {
                display: block;
                padding: 13px 16px;

                border: 1px solid rgba(255,255,255,.08);
                border-radius: 9px;

                color: #e1eff0;
                font-size: .88rem;
                font-weight: 700;
            }

            .mobile-nav a:hover {
                background: rgba(255,255,255,.08);
            }

            .mobile-nav .mobile-primary {
                background: #b3efe2;
                color: #0b454d;
            }

            .hero-inner {
                grid-template-columns: 1fr;
                gap: 54px;

                padding-top: 75px;
                padding-bottom: 88px;
            }

            .hero-copy {
                max-width: 750px;
            }

            .journey-visual {
                max-width: 610px;
                justify-self: center;
            }

            .feature-grid {
                grid-template-columns: repeat(2,minmax(0,1fr));
            }

            .process-grid {
                gap: 20px;
            }

        }

        @media (max-width: 650px) {

            .container {
                width: min(100% - 36px,1220px);
            }

            .header-inner {
                min-height: 77px;
            }

            .brand-name {
                font-size: .79rem;
            }

            .brand-mark {
                width: 39px;
                height: 39px;
            }

            .hero-inner {
                padding-top: 62px;
                padding-bottom: 68px;
            }

            .hero-title {
                font-size: clamp(2.7rem,10vw,3.7rem);
            }

            .hero-description {
                font-size: .94rem;
            }

            .hero-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .primary-button,
            .outline-button {
                width: 100%;
            }

            .hero-caption {
                margin-top: 24px;
            }

            .journey-content {
                padding: 22px;
            }

            .journey-visual::before {
                display: none;
            }

            .journey-title {
                font-size: 1.18rem;
            }

            .section,
            .process-section,
            .access-section {
                padding: 74px 0;
            }

            .section-title {
                font-size: 2rem;
            }

            .feature-grid,
            .process-grid,
            .access-grid {
                grid-template-columns: 1fr;
            }

            .feature-card {
                padding: 29px;
            }

            .process-grid {
                gap: 38px;
            }

            .access-card {
                min-height: 310px;
                padding: 30px;
            }

            .bottom-cta {
                margin-bottom: 70px;
                padding: 42px 28px;
                border-radius: 20px;
            }

            .bottom-cta h2 {
                font-size: 2rem;
            }

            .footer-main {
                flex-direction: column;
            }

        }

        @media (prefers-reduced-motion: reduce) {

            html {
                scroll-behavior: auto;
            }

            *,
            *::before,
            *::after {
                transition-duration: .01ms !important;
            }

        }

    </style>
</head>

<body>

<a href="#mainContent" class="skip-link">
    Skip to main content
</a>

<!-- Header -->

<header class="site-header">

    <div class="container">

        <div class="header-inner">

            <a href="index.php" class="brand">

                <span class="brand-mark" aria-hidden="true">

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.2"
                        stroke-linecap="round"
                    >
                        <path d="M12 3v18M3 12h18"/>
                    </svg>

                </span>

                <span class="brand-text">
                    Clinic Appointment<br>
                    System
                </span>

            </a>

            <nav class="desktop-nav" aria-label="Main navigation">

                <a href="#features" class="nav-link">
                    Features
                </a>

                <a href="#howItWorks" class="nav-link">
                    How It Works
                </a>

                <a href="#access" class="nav-link">
                    Who It's For
                </a>

            </nav>

            <div class="header-actions">

                <a href="login.php" class="sign-in-link">
                    Sign In
                </a>

                <a href="register.php" class="header-cta">

                    Create Account

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.9"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M5 12h14"/>
                        <path d="m13 6 6 6-6 6"/>
                    </svg>

                </a>

            </div>

            <button
                type="button"
                id="menuToggle"
                class="menu-toggle"
                aria-label="Open navigation menu"
                aria-expanded="false"
                aria-controls="mobileNavigation"
            >

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    aria-hidden="true"
                >
                    <path d="M4 7h16M4 12h16M4 17h16"/>
                </svg>

            </button>

        </div>

        <nav
            id="mobileNavigation"
            class="mobile-nav"
            aria-label="Mobile navigation"
        >

            <a href="#features">
                Features
            </a>

            <a href="#howItWorks">
                How It Works
            </a>

            <a href="#access">
                Who It's For
            </a>

            <a href="login.php">
                Sign In
            </a>

            <a
                href="register.php"
                class="mobile-primary"
            >
                Create Patient Account
            </a>

        </nav>

    </div>

</header>

<main id="mainContent">

    <!-- Main hero -->

    <section class="hero">

        <div class="container hero-inner">

            <div class="hero-copy">

                <span class="eyebrow">

                    <span
                        class="eyebrow-line"
                        aria-hidden="true"
                    ></span>

                    A Better Way to Connect with Care

                </span>

                <h1 class="hero-title">

                    Less time arranging.

                    <span>
                        More time for care.
                    </span>

                </h1>

                <p class="hero-description">

                    Discover doctors, find available
                    appointment times, and manage your
                    visits in one connected clinic
                    scheduling experience.

                </p>

                <div class="hero-actions">

                    <a
                        href="register.php"
                        class="primary-button"
                    >

                        Get Started

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M5 12h14"/>
                            <path d="m13 6 6 6-6 6"/>
                        </svg>

                    </a>

                    <a
                        href="login.php"
                        class="outline-button"
                    >
                        Sign In to Your Account
                    </a>

                </div>

                <p class="hero-caption">

                    One connected platform for patients,
                    doctors, and authorized clinic staff.

                </p>

            </div>

            <!-- Illustrative booking workflow -->

            <div
                class="journey-visual"
                aria-label="Illustration showing the three steps of appointment booking"
            >

                <div class="journey-window">

                    <div class="window-top">

                        <div class="window-title">

                            <span class="window-icon">

                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                >
                                    <rect
                                        x="3" y="5"
                                        width="18" height="16"
                                        rx="2"
                                    />
                                    <path d="M7 3v4M17 3v4M3 10h18"/>
                                </svg>

                            </span>

                            Appointment Journey

                        </div>

                        <div
                            class="window-dots"
                            aria-hidden="true"
                        >
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>

                    </div>

                    <div class="journey-content">

                        <span class="journey-kicker">
                            Patient Experience
                        </span>

                        <h2 class="journey-title">
                            Care begins with a few simple steps.
                        </h2>

                        <p class="journey-description">
                            An overview of how appointment
                            booking works in the patient portal.
                        </p>

                        <div class="journey-step">

                            <span
                                class="step-icon"
                                aria-hidden="true"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                >
                                    <circle cx="11" cy="11" r="7"/>
                                    <path d="m20 20-4-4"/>
                                </svg>
                            </span>

                            <div class="step-text">

                                <span class="step-number">
                                    STEP 01
                                </span>

                                <strong>
                                    Discover a Doctor
                                </strong>

                                <p>
                                    Search by doctor name
                                    or medical specialty.
                                </p>

                            </div>

                        </div>

                        <div class="journey-step">

                            <span
                                class="step-icon blue"
                                aria-hidden="true"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                >
                                    <rect
                                        x="3" y="5"
                                        width="18" height="16"
                                        rx="2"
                                    />
                                    <path d="M7 3v4M17 3v4M3 10h18"/>
                                    <path d="M8 15h8"/>
                                </svg>
                            </span>

                            <div class="step-text">

                                <span class="step-number">
                                    STEP 02
                                </span>

                                <strong>
                                    Choose an Available Time
                                </strong>

                                <p>
                                    Review the doctor's
                                    available appointment slots.
                                </p>

                            </div>

                        </div>

                        <div class="journey-step">

                            <span
                                class="step-icon purple"
                                aria-hidden="true"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                >
                                    <rect
                                        x="3" y="5"
                                        width="18" height="16"
                                        rx="2"
                                    />
                                    <path d="M7 3v4M17 3v4M3 10h18"/>
                                    <path d="m8 16 2 2 5-5"/>
                                </svg>
                            </span>

                            <div class="step-text">

                                <span class="step-number">
                                    STEP 03
                                </span>

                                <strong>
                                    Review Your Booking
                                </strong>

                                <p>
                                    View appointment details
                                    and manage your schedule.
                                </p>

                            </div>

                        </div>

                    </div>

                    <div class="window-footer">

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                        >
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 8v4"/>
                            <path d="M12 16h.01"/>
                        </svg>

                        Appointment information is managed
                        through the secure account portal.

                    </div>

                </div>

            </div>

        </div>

    </section>

    <!-- Features -->

    <section
        class="section"
        id="features"
    >

        <div class="container">

            <div class="section-heading">

                <span class="section-kicker">
                    Designed Around Your Day
                </span>

                <h2 class="section-title">
                    Everything you need to stay
                    connected to your appointments.
                </h2>

                <p class="section-description">
                    From finding a doctor to reviewing
                    upcoming visits, the Clinic Appointment
                    System brings essential scheduling
                    tools together.
                </p>

            </div>

            <div class="feature-grid">

                <article class="feature-card">

                    <span
                        class="feature-icon"
                        aria-hidden="true"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <circle cx="11" cy="11" r="7"/>
                            <path d="m20 20-4-4"/>
                            <path d="M8 11h6"/>
                        </svg>
                    </span>

                    <h3>
                        Doctor Discovery
                    </h3>

                    <p>
                        Search for doctors by name or
                        specialty, review their professional
                        information, and explore published
                        availability.
                    </p>

                </article>

                <article class="feature-card">

                    <span
                        class="feature-icon blue"
                        aria-hidden="true"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <rect
                                x="3" y="5"
                                width="18" height="16"
                                rx="2"
                            />
                            <path d="M7 3v4M17 3v4M3 10h18"/>
                            <path d="m9 15 2 2 4-4"/>
                        </svg>
                    </span>

                    <h3>
                        Appointment Scheduling
                    </h3>

                    <p>
                        Choose available appointment
                        times and review your booking
                        details in an organized
                        patient portal.
                    </p>

                </article>

                <article class="feature-card">

                    <span
                        class="feature-icon purple"
                        aria-hidden="true"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <rect
                                x="4" y="3"
                                width="16" height="18"
                                rx="2"
                            />
                            <path d="M8 8h8M8 12h8M8 16h5"/>
                        </svg>
                    </span>

                    <h3>
                        Organized Visit History
                    </h3>

                    <p>
                        Keep upcoming, previous, and
                        cancelled appointments together,
                        with available management options
                        based on clinic scheduling rules.
                    </p>

                </article>

            </div>

        </div>

    </section>

    <!-- How booking works -->

    <section
        class="process-section"
        id="howItWorks"
    >

        <div class="container">

            <div class="section-heading">

                <span class="section-kicker">
                    How It Works
                </span>

                <h2 class="section-title">
                    A simpler path from searching
                    to scheduling.
                </h2>

                <p class="section-description">
                    A clear appointment process,
                    designed to help patients
                    move through each step.
                </p>

            </div>

            <div class="process-grid">

                <div class="process-item">

                    <span class="process-number">
                        01
                    </span>

                    <div class="process-rule"></div>

                    <h3>
                        Create Your Patient Account
                    </h3>

                    <p>
                        Register with your personal
                        information and sign in
                        to access the patient portal.
                    </p>

                </div>

                <div class="process-item">

                    <span class="process-number">
                        02
                    </span>

                    <div class="process-rule"></div>

                    <h3>
                        Find the Right Doctor
                    </h3>

                    <p>
                        Browse matching doctors,
                        view their qualifications,
                        and check available
                        appointment times.
                    </p>

                </div>

                <div class="process-item">

                    <span class="process-number">
                        03
                    </span>

                    <div class="process-rule"></div>

                    <h3>
                        Book and Manage Your Visit
                    </h3>

                    <p>
                        Select an available time,
                        review your appointment,
                        and access your schedule
                        from one place.
                    </p>

                </div>

            </div>

        </div>

    </section>

    <!-- Portal entry points -->

    <section
        class="access-section"
        id="access"
    >

        <div class="container">

            <div class="section-heading">

                <span class="section-kicker">
                    Connected Workspaces
                </span>

                <h2 class="section-title">
                    The right tools for
                    every clinic role.
                </h2>

                <p class="section-description">
                    Patients, doctors, and clinic
                    staff access tools relevant
                    to their accounts.
                </p>

            </div>

            <div class="access-grid">

                <article class="access-card patient">

                    <span
                        class="access-icon"
                        aria-hidden="true"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 21a8 8 0 0 1 16 0"/>
                        </svg>
                    </span>

                    <span class="access-label">
                        For Patients
                    </span>

                    <h3>
                        Your care, organized.
                    </h3>

                    <p>
                        Find doctors, book appointments,
                        review your visits, and keep
                        your patient profile up to date.
                    </p>

                    <a
                        href="register.php"
                        class="access-link"
                    >
                        Create Patient Account

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M5 12h14"/>
                            <path d="m13 6 6 6-6 6"/>
                        </svg>

                    </a>

                </article>

                <article class="access-card team">

                    <span
                        class="access-icon"
                        aria-hidden="true"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <rect
                                x="3" y="4"
                                width="18" height="17"
                                rx="2"
                            />
                            <path d="M8 9h8M8 13h8M8 17h4"/>
                        </svg>
                    </span>

                    <span class="access-label">
                        For Clinic Teams
                    </span>

                    <h3>
                        One connected workspace.
                    </h3>

                    <p>
                        Authorized doctors,
                        receptionists, and
                        administrators can sign
                        in to access their
                        respective scheduling
                        and management tools.
                    </p>

                    <a
                        href="login.php"
                        class="access-link"
                    >
                        Clinic Team Sign In

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M5 12h14"/>
                            <path d="m13 6 6 6-6 6"/>
                        </svg>

                    </a>

                </article>

            </div>

        </div>

    </section>

    <!-- Final call to action -->

    <div class="container">

        <section class="bottom-cta">

            <h2>
                A more connected way
                to manage your care.
            </h2>

            <p>
                Get started with your patient account
                or sign in to continue managing
                your appointments.
            </p>

            <a
                href="register.php"
                class="primary-button"
            >

                Create Your Account

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M5 12h14"/>
                    <path d="m13 6 6 6-6 6"/>
                </svg>

            </a>

        </section>

    </div>

</main>

<!-- Footer -->

<footer class="site-footer">

    <div class="container">

        <div class="footer-main">

            <div>

                <div class="footer-brand">

                    <span
                        class="brand-mark"
                        aria-hidden="true"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.2"
                            stroke-linecap="round"
                        >
                            <path d="M12 3v18M3 12h18"/>
                        </svg>
                    </span>

                    <span>
                        Clinic Appointment<br>
                        System
                    </span>

                </div>

                <p class="footer-description">
                    Connecting patients and
                    clinic teams through a
                    more organized appointment
                    scheduling experience.
                </p>

            </div>

            <nav
                class="footer-links"
                aria-label="Footer navigation"
            >

                <a href="index.php">
                    Home
                </a>

                <a href="#features">
                    Features
                </a>

                <a href="login.php">
                    Sign In
                </a>

                <a href="register.php">
                    Register
                </a>

            </nav>

        </div>

        <div class="footer-bottom">

            <span>
                Clinic Appointment System
            </span>

            <span>
                Appointment Scheduling Platform
            </span>

        </div>

    </div>

</footer>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const toggle = document.getElementById('menuToggle');

    const mobileNav = document.getElementById(
        'mobileNavigation'
    );

    if (!toggle || !mobileNav) {
        return;
    }

    function closeMenu() {

        mobileNav.classList.remove('is-open');

        toggle.setAttribute(
            'aria-expanded',
            'false'
        );

        toggle.setAttribute(
            'aria-label',
            'Open navigation menu'
        );
    }

    toggle.addEventListener('click', function () {

        const isOpen = mobileNav.classList.toggle(
            'is-open'
        );

        toggle.setAttribute(
            'aria-expanded',
            isOpen ? 'true' : 'false'
        );

        toggle.setAttribute(
            'aria-label',
            isOpen
                ? 'Close navigation menu'
                : 'Open navigation menu'
        );

    });

    mobileNav.querySelectorAll('a').forEach(
        function (link) {

            link.addEventListener(
                'click',
                closeMenu
            );

        }
    );

    document.addEventListener('keydown', function (event) {

        if (event.key === 'Escape') {
            closeMenu();
        }

    });

});
</script>

</body>
</html>