<?php
session_start();

$loginError = $_SESSION['login_error'] ?? '';
$oldEmail = $_SESSION['old_login_email'] ?? '';

unset($_SESSION['login_error']);
unset($_SESSION['old_login_email']);

function loginEscape($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Sign In | Clinic Appointment System</title>

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
            --navy-deep: #0b2438;
            --navy-light: #173e61;
            --teal: #087d85;
            --teal-bright: #8de2d4;
            --ink: #173a50;
            --muted: #718696;
            --line: #dce7ec;
            --surface: #f7fafb;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html {
            font-size: 16px;
        }

        body {
            margin: 0;
            min-height: 100vh;

            font-family:
                "DM Sans",
                "Segoe UI",
                sans-serif;

            color: var(--ink);

            background:
                radial-gradient(
                    ellipse at 12% 18%,
                    rgba(81, 190, 181, 0.19),
                    transparent 35%
                ),
                radial-gradient(
                    ellipse at 88% 80%,
                    rgba(32, 91, 133, 0.12),
                    transparent 35%
                ),
                #f2f7f9;

            -webkit-font-smoothing: antialiased;
        }

        button,
        input {
            font: inherit;
        }

        button {
            cursor: pointer;
        }

        a {
            text-decoration: none;
        }

        /* Main layout */

        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 38px 24px;
        }

        .auth-shell {
            width: 100%;
            max-width: 1160px;
            min-height: 670px;

            display: grid;
            grid-template-columns:
                minmax(0, 1fr)
                minmax(0, 1fr);

            overflow: hidden;

            background: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 28px;

            box-shadow:
                0 34px 100px rgba(17, 55, 79, 0.13),
                0 8px 26px rgba(17, 55, 79, 0.045);

            animation: appear 0.55s ease-out both;
        }

        /* Brand side */

        .brand-panel {
            position: relative;
            isolation: isolate;
            overflow: hidden;

            display: flex;
            flex-direction: column;
            justify-content: space-between;

            padding: 43px 48px;

            color: #ffffff;

            background:
                radial-gradient(
                    ellipse at 98% 85%,
                    rgba(40, 177, 170, 0.31),
                    transparent 48%
                ),
                linear-gradient(
                    150deg,
                    #102d46 0%,
                    #153a55 50%,
                    #0c3447 100%
                );
        }

        /* Subtle architectural pattern */

        .brand-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;

            background-image:
                linear-gradient(
                    rgba(255, 255, 255, 0.035) 1px,
                    transparent 1px
                ),
                linear-gradient(
                    90deg,
                    rgba(255, 255, 255, 0.035) 1px,
                    transparent 1px
                );

            background-size: 65px 65px;
            opacity: 0.48;
            pointer-events: none;
        }

        .brand-panel::after {
            content: "";
            position: absolute;
            z-index: -1;

            width: 520px;
            height: 520px;

            right: -320px;
            bottom: -300px;

            border: 1px solid rgba(152, 232, 221, 0.18);
            border-radius: 50%;

            box-shadow:
                0 0 0 60px rgba(152, 232, 221, 0.025),
                0 0 0 135px rgba(152, 232, 221, 0.025);

            pointer-events: none;
        }

        /* Brand identity */

        .brand-header {
            position: relative;
            z-index: 1;

            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-mark {
            width: 43px;
            height: 43px;
            flex-shrink: 0;

            position: relative;

            border: 1px solid rgba(151, 233, 219, 0.36);
            border-radius: 13px;

            background: rgba(123, 220, 207, 0.11);
        }

        .brand-mark::before,
        .brand-mark::after {
            content: "";
            position: absolute;
            background: #91e5d9;
            border-radius: 2px;
        }

        .brand-mark::before {
            width: 7px;
            height: 21px;
            left: 17px;
            top: 10px;
        }

        .brand-mark::after {
            width: 21px;
            height: 7px;
            left: 10px;
            top: 17px;
        }

        .brand-name {
            max-width: 225px;

            font-family:
                "Manrope",
                "Segoe UI",
                sans-serif;

            font-size: 0.93rem;
            font-weight: 750;
            letter-spacing: -0.035em;
            line-height: 1.45;

            color: #ffffff;
        }

        /* Brand message */

        .brand-content {
            position: relative;
            z-index: 1;

            padding: 52px 0;
        }

        .brand-eyebrow {
            display: block;
            margin-bottom: 24px;

            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;

            color: #91e2d7;
        }

        .brand-heading {
            max-width: 470px;
            margin: 0 0 27px;

            font-family:
                "Manrope",
                "Segoe UI",
                sans-serif;

            font-size: clamp(3rem, 4.4vw, 4.65rem);
            font-weight: 800;
            letter-spacing: -0.075em;
            line-height: 1.12;

            color: #ffffff;
        }

        .brand-heading span {
            display: block;
            color: var(--teal-bright);
        }

        .brand-description {
            max-width: 385px;

            margin: 0;

            color: #c6dce5;
            font-size: 1.02rem;
            line-height: 1.9;
            font-weight: 400;
        }

        .brand-rule {
            width: 100%;
            height: 1px;

            margin: 32px 0 25px;

            background: linear-gradient(
                to right,
                rgba(159, 229, 221, 0.45),
                rgba(159, 229, 221, 0)
            );
        }

        .brand-caption {
            max-width: 330px;
            margin: 0;

            color: #d4e8ec;
            font-size: 0.88rem;
            line-height: 1.8;
        }

        .brand-footer {
            position: relative;
            z-index: 1;

            font-size: 0.69rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;

            color: rgba(221, 241, 242, 0.66);
        }

        /* Login side */

        .form-panel {
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;

            padding: 50px 64px 35px;

            background: #ffffff;
        }

        .form-inner {
            width: 100%;
            max-width: 420px;
            margin: auto;
        }

        .form-eyebrow {
            display: block;

            margin-bottom: 17px;

            color: var(--teal);
            font-size: 0.73rem;
            font-weight: 800;
            letter-spacing: 0.17em;
            text-transform: uppercase;
        }

        .form-title {
            margin: 0 0 14px;

            font-family:
                "Manrope",
                "Segoe UI",
                sans-serif;

            font-size: clamp(2rem, 2.8vw, 2.55rem);
            font-weight: 800;
            line-height: 1.22;
            letter-spacing: -0.055em;

            color: #13354d;
        }

        .form-description {
            margin: 0 0 38px;

            color: var(--muted);
            font-size: 0.95rem;
            line-height: 1.8;
        }

        /* Error message */

        .login-error {
            margin-bottom: 26px;
            padding: 16px 18px;

            background: #fff3f2;
            border: 1px solid #f1d0cd;
            border-radius: 12px;

            color: #9d3540;
            font-size: 0.89rem;
            line-height: 1.65;
        }

        .login-error strong {
            display: block;
            margin-bottom: 5px;
        }

        /* Form fields */

        .field {
            margin-bottom: 23px;
        }

        .field-label {
            display: block;
            margin-bottom: 10px;

            color: #294a60;
            font-size: 0.89rem;
            font-weight: 700;
        }

        .field-input {
            display: block;

            width: 100%;
            min-height: 55px;

            padding: 14px 16px;

            border: 1px solid var(--line);
            border-radius: 12px;

            background: var(--surface);
            color: #17394f;

            font-size: 0.96rem;

            outline: none;

            transition:
                border-color 0.2s ease,
                background 0.2s ease,
                box-shadow 0.2s ease;
        }

        .field-input::placeholder {
            color: #9aabb5;
        }

        .field-input:hover {
            border-color: #bed4dc;
            background: #ffffff;
        }

        .field-input:focus {
            background: #ffffff;
            border-color: #0e8790;

            box-shadow:
                0 0 0 4px rgba(8, 125, 133, 0.11);
        }

        /* Password field */

        .password-wrap {
            position: relative;
        }

        .password-wrap .field-input {
            padding-right: 85px;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 12px;

            transform: translateY(-50%);

            min-width: 50px;

            padding: 9px 7px;

            border: none;
            border-radius: 6px;

            background: transparent;

            color: var(--teal);
            font-size: 0.84rem;
            font-weight: 700;
        }

        .password-toggle:hover {
            background: #e9f5f4;
        }

        .password-toggle:focus-visible {
            outline: 2px solid var(--teal);
            outline-offset: 2px;
        }

        /* Sign in button */

        .submit-button {
            width: 100%;
            min-height: 56px;

            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;

            margin-top: 12px;

            padding: 14px 20px;

            border: 1px solid var(--teal);
            border-radius: 12px;

            background: linear-gradient(
                115deg,
                #087d85 0%,
                #096c78 100%
            );

            color: #ffffff;

            font-family:
                "Manrope",
                "Segoe UI",
                sans-serif;

            font-size: 0.98rem;
            font-weight: 750;

            box-shadow:
                0 11px 25px rgba(8, 125, 133, 0.17);

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                filter 0.2s ease;
        }

        .submit-button:hover {
            transform: translateY(-2px);
            filter: brightness(1.07);

            box-shadow:
                0 15px 30px rgba(8, 125, 133, 0.23);
        }

        .submit-button:active {
            transform: translateY(0);
        }

        .submit-button:focus-visible {
            outline: 3px solid rgba(8, 125, 133, 0.3);
            outline-offset: 3px;
        }

        .submit-arrow {
            font-size: 1.2rem;
            line-height: 1;
        }

        /* Registration section */

        .register-section {
            margin-top: 34px;
            padding-top: 27px;

            border-top: 1px solid #e7eef1;

            text-align: center;
        }

        .register-question {
            margin: 0 0 11px;

            color: #74899a;
            font-size: 0.9rem;
        }

        .register-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;

            color: var(--teal);
            font-size: 0.94rem;
            font-weight: 750;
        }

        .register-link:hover {
            color: #075a63;
            text-decoration: underline;
            text-underline-offset: 4px;
        }

        .form-footer {
            margin-top: 42px;

            color: #a0afb9;
            text-align: center;
            font-size: 0.77rem;
        }

        /* Motion */

        @keyframes appear {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Tablet */

        @media (max-width: 900px) {
            .auth-shell {
                grid-template-columns: 1fr;
                max-width: 660px;
            }

            .brand-panel {
                padding: 35px 40px;
                min-height: 300px;
            }

            .brand-content {
                padding: 40px 0 20px;
            }

            .brand-heading {
                max-width: 550px;
                font-size: 2.8rem;
            }

            .brand-description {
                max-width: 520px;
                font-size: 0.95rem;
            }

            .brand-rule,
            .brand-caption,
            .brand-footer {
                display: none;
            }

            .form-panel {
                padding: 46px 40px 35px;
            }
        }

        /* Mobile */

        @media (max-width: 575px) {
            .auth-page {
                padding: 14px 10px;
            }

            .auth-shell {
                border-radius: 20px;
            }

            .brand-panel {
                min-height: 245px;
                padding: 28px 25px;
            }

            .brand-mark {
                width: 38px;
                height: 38px;
            }

            .brand-mark::before {
                left: 15px;
                top: 8px;
            }

            .brand-mark::after {
                left: 8px;
                top: 15px;
            }

            .brand-name {
                font-size: 0.84rem;
            }

            .brand-content {
                padding: 28px 0 10px;
            }

            .brand-eyebrow {
                font-size: 0.65rem;
                margin-bottom: 13px;
            }

            .brand-heading {
                font-size: 2.2rem;
                letter-spacing: -0.065em;
                margin-bottom: 16px;
            }

            .brand-description {
                font-size: 0.84rem;
                line-height: 1.75;
            }

            .form-panel {
                padding: 36px 25px 28px;
            }

            .form-title {
                font-size: 1.85rem;
            }

            .form-description {
                margin-bottom: 28px;
                font-size: 0.89rem;
            }

            .field-input {
                min-height: 52px;
            }

            .form-footer {
                margin-top: 30px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .auth-shell {
                animation: none;
            }

            *,
            *::before,
            *::after {
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body>

<main class="auth-page">

    <div class="auth-shell">

        <!-- Brand panel -->

        <section class="brand-panel">

            <div class="brand-header">

                <div
                    class="brand-mark"
                    aria-hidden="true"
                ></div>

                <div class="brand-name">
                    Clinic Appointment System
                </div>

            </div>

            <div class="brand-content">

                <span class="brand-eyebrow">
                    Your healthcare workspace
                </span>

                <h1 class="brand-heading">
                    More time
                    <span>for care.</span>
                </h1>

                <p class="brand-description">
                    Book appointments, organize schedules,
                    and manage clinic information through
                    one clear, modern platform.
                </p>

                <div class="brand-rule"></div>

                <p class="brand-caption">
                    Designed to make everyday healthcare
                    management simpler for patients
                    and clinic teams.
                </p>

            </div>

            <div class="brand-footer">
                Clinic Appointment System
            </div>

        </section>

        <!-- Login form -->

        <section class="form-panel">

            <div class="form-inner">

                <span class="form-eyebrow">
                    Welcome back
                </span>

                <h2 class="form-title">
                    Sign in to your account
                </h2>

                <p class="form-description">
                    Enter your email address and password
                    to continue.
                </p>

                <?php if ($loginError !== ''): ?>

                    <div
                        class="login-error"
                        role="alert"
                    >
                        <strong>
                            Unable to sign in
                        </strong>

                        <?= loginEscape($loginError) ?>
                    </div>

                <?php endif; ?>

                <form
                    action="actions/login_process.php"
                    method="POST"
                    novalidate
                >

                    <div class="field">

                        <label
                            for="email"
                            class="field-label"
                        >
                            Email Address
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="field-input"
                            placeholder="Enter your email address"
                            autocomplete="email"
                            value="<?= loginEscape($oldEmail) ?>"
                            required
                        >

                    </div>

                    <div class="field">

                        <label
                            for="password"
                            class="field-label"
                        >
                            Password
                        </label>

                        <div class="password-wrap">

                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="field-input"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                id="togglePassword"
                                aria-label="Show password"
                                aria-pressed="false"
                            >
                                Show
                            </button>

                        </div>

                    </div>

                    <button
                        type="submit"
                        class="submit-button"
                    >
                        Sign In

                        <span
                            class="submit-arrow"
                            aria-hidden="true"
                        >
                            &rarr;
                        </span>

                    </button>

                </form>

                <div class="register-section">

                    <p class="register-question">
                        New to the clinic?
                    </p>

                    <a
                        href="register.php"
                        class="register-link"
                    >
                        Create a Patient Account

                        <span aria-hidden="true">
                            &rarr;
                        </span>
                    </a>

                </div>

            </div>

            <div class="form-footer">
                Clinic Appointment System
            </div>

        </section>

    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const toggle = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    if (!toggle || !password) {
        return;
    }

    toggle.addEventListener('click', function () {

        const shouldShow = password.type === 'password';

        password.type = shouldShow
            ? 'text'
            : 'password';

        toggle.textContent = shouldShow
            ? 'Hide'
            : 'Show';

        toggle.setAttribute(
            'aria-label',
            shouldShow ? 'Hide password' : 'Show password'
        );

        toggle.setAttribute(
            'aria-pressed',
            shouldShow ? 'true' : 'false'
        );

    });

});
</script>

</body>
</html>