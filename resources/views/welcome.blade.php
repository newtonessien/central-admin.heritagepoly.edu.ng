<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>HERITAGE POLYTECHNIC — Central Administration</title>

    <meta
        name="description"
        content="Heritage Polytechnic Central Administration Portal"
    >

    <meta name="theme-color" content="#15803d">

    <link rel="icon" type="image/x-icon" href="/logo/icon.png">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d'
                        }
                    }
                }
            }
        };
    </script>

    <style>
        body {
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        .system-card {
            transition:
                transform 180ms ease,
                box-shadow 180ms ease,
                border-color 180ms ease;
        }

        .system-card:hover {
            transform: translateY(-2px);
        }

        @media (prefers-reduced-motion: reduce) {
            .system-card {
                transition: none;
            }

            .system-card:hover {
                transform: none;
            }
        }
    </style>
</head>


<body class="min-h-screen bg-gray-50 text-gray-900
             dark:bg-gray-950 dark:text-gray-100">

<div class="min-h-screen flex flex-col">


    <!-- ========================================================= -->
    <!-- HEADER -->
    <!-- ========================================================= -->

    <header
        class="sticky top-0 z-50
               border-b border-gray-200
               bg-white/95 backdrop-blur
               dark:border-gray-800
               dark:bg-gray-950/95">

        <div class="max-w-7xl mx-auto px-4 sm:px-6">

            <div class="h-16 flex items-center justify-between">


                <!-- Brand -->

                <a
                    href="/"
                    class="flex items-center gap-3
                           min-w-0"
                    aria-label="Heritage Polytechnic Central Administration">

                    <div
                        class="h-10 w-10 shrink-0
                               rounded-lg
                               bg-green-50
                               dark:bg-green-950/40
                               flex items-center justify-center">

                        <img
                            src="/logo/icon.png"
                            alt="Heritage Polytechnic"
                            class="h-8 w-8 object-contain"
                        >

                    </div>


                    <div class="min-w-0">

                        <div
                            class="font-bold tracking-tight
                                   text-gray-950
                                   dark:text-white
                                   truncate">

                            Heritage Polytechnic

                        </div>

                        <div
                            class="text-xs text-gray-500
                                   dark:text-gray-400">

                            Central Administration

                        </div>

                    </div>

                </a>


                <!-- Header Actions -->

                <div class="flex items-center gap-2">


                    <!-- Theme Toggle -->

                    <button
                        id="themeToggle"
                        type="button"
                        aria-label="Switch to dark mode"
                        aria-pressed="false"
                        class="inline-flex items-center justify-center
                               h-10 w-10
                               rounded-lg
                               border border-gray-200
                               bg-white
                               text-gray-600
                               hover:bg-gray-50
                               hover:text-gray-900
                               focus:outline-none
                               focus:ring-2
                               focus:ring-green-600
                               focus:ring-offset-2
                               dark:border-gray-700
                               dark:bg-gray-900
                               dark:text-gray-300
                               dark:hover:bg-gray-800
                               dark:hover:text-white
                               dark:focus:ring-offset-gray-950
                               transition">

                        <!-- Moon -->

                        <svg
                            id="moonIcon"
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            aria-hidden="true">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M21 12.79A9 9 0 1111.21 3
                                   7 7 0 0021 12.79z"
                            />

                        </svg>


                        <!-- Sun -->

                        <svg
                            id="sunIcon"
                            xmlns="http://www.w3.org/2000/svg"
                            class="hidden h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            aria-hidden="true">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3
                                   m15.364-6.364l-.707.707M6.343
                                   17.657l-.707.707m12.728 0l-.707-.707
                                   M6.343 6.343l-.707-.707
                                   M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                            />

                        </svg>

                        <span class="sr-only">
                            Toggle dark mode
                        </span>

                    </button>


                    <!-- Login -->

                    <a
                        href="/login"
                        class="inline-flex items-center gap-2
                               h-10 px-4
                               rounded-lg
                               bg-green-700
                               text-white
                               text-sm font-semibold
                               hover:bg-green-800
                               focus:outline-none
                               focus:ring-2
                               focus:ring-green-600
                               focus:ring-offset-2
                               dark:focus:ring-offset-gray-950
                               shadow-sm
                               transition">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            aria-hidden="true">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 12H3m0 0l4-4m-4 4l4 4
                                   m8-9V5a2 2 0 012-2h2a2 2 0 012 2v14
                                   a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2"
                            />

                        </svg>

                        <span>Sign in</span>

                    </a>

                </div>

            </div>

        </div>

    </header>



    <!-- ========================================================= -->
    <!-- MAIN -->
    <!-- ========================================================= -->

    <main class="flex-1">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-12">


            <!-- ================================================= -->
            <!-- WELCOME -->
            <!-- ================================================= -->

            <section class="mb-10">

                <div
                    class="flex flex-col
                           sm:flex-row
                           sm:items-end
                           sm:justify-between
                           gap-5">


                    <!-- Introduction -->

                    <div>

                        <div
                            class="flex items-center gap-2 mb-3">

                            <span
                                class="h-2.5 w-2.5
                                       rounded-full
                                       bg-green-600
                                       dark:bg-green-500"
                                aria-hidden="true">
                            </span>

                            <span
                                class="text-sm
                                       font-medium
                                       text-green-700
                                       dark:text-green-400">

                                Central Administration

                            </span>

                        </div>


                        <h1
                            id="greeting"
                            class="text-3xl
                                   sm:text-4xl
                                   font-bold
                                   tracking-tight
                                   text-gray-950
                                   dark:text-white">

                            Welcome, Admin

                        </h1>


                        <p
                            class="mt-3
                                   max-w-2xl
                                   text-base
                                   leading-7
                                   text-gray-600
                                   dark:text-gray-400">

                            Manage Heritage Polytechnic's academic,
                            financial and administrative systems from
                            one secure control centre.

                        </p>

                    </div>


                    <!-- Date -->

                    <div
                        class="shrink-0
                               text-sm
                               text-gray-500
                               dark:text-gray-400">

                        <div class="flex items-center gap-2">

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                aria-hidden="true">

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M8 7V3m8 4V3m-9 8h10
                                       M5 21h14a2 2 0 002-2V7a2 2
                                       0 00-2-2H5a2 2 0 00-2 2v12
                                       a2 2 0 002 2z"
                                />

                            </svg>

                            <time id="currentDate"></time>

                        </div>

                    </div>

                </div>

            </section>



            <!-- ================================================= -->
            <!-- SYSTEMS -->
            <!-- ================================================= -->

            <section
                aria-labelledby="systems-heading">


                <!-- Section Heading -->

                <div class="mb-5">

                    <h2
                        id="systems-heading"
                        class="text-xl
                               font-semibold
                               text-gray-900
                               dark:text-white">

                        Administration Systems

                    </h2>

                    <p
                        class="mt-1
                               text-sm
                               text-gray-500
                               dark:text-gray-400">

                        Select a system to continue.

                    </p>

                </div>



                <!-- System Cards -->

                <div
                    class="grid
                           grid-cols-1
                           sm:grid-cols-2
                           lg:grid-cols-3
                           gap-5">


                    <!-- ================================================= -->
                    <!-- RMS -->
                    <!-- ================================================= -->

                    <a
                        href="https://rms.heritagepoly.edu.ng/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="system-card
                               group
                               bg-white
                               dark:bg-gray-900
                               border
                               border-gray-200
                               dark:border-gray-800
                               rounded-xl
                               p-6
                               hover:border-green-400
                               dark:hover:border-green-700
                               hover:shadow-md
                               focus:outline-none
                               focus:ring-2
                               focus:ring-green-600
                               focus:ring-offset-2
                               dark:focus:ring-offset-gray-950">


                        <div
                            class="flex
                                   items-center
                                   justify-between">


                            <div
                                class="h-11
                                       w-11
                                       rounded-lg
                                       bg-green-50
                                       text-green-700
                                       dark:bg-green-950/40
                                       dark:text-green-400
                                       flex
                                       items-center
                                       justify-center">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    aria-hidden="true">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M12 14l9-5-9-5-9 5
                                           9 5zm0 0v7m0-7L3 9m9
                                           5l9-5"
                                    />

                                </svg>

                            </div>


                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5
                                       text-gray-300
                                       group-hover:text-green-600
                                       dark:group-hover:text-green-400
                                       transition"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                aria-hidden="true">

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M7 17L17 7M7 7h10v10"
                                />

                            </svg>

                        </div>


                        <h3
                            class="mt-5
                                   text-lg
                                   font-semibold
                                   text-gray-900
                                   dark:text-white">

                            Result Management System

                        </h3>


                        <p
                            class="mt-1
                                   text-sm
                                   leading-6
                                   text-gray-500
                                   dark:text-gray-400">

                            Academic results and examination management.

                        </p>

                    </a>



                    <!-- ================================================= -->
                    <!-- LEGACY ADMIN -->
                    <!-- ================================================= -->

                    <a
                        href="https://legacy.heritagepoly.edu.ng/console/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="system-card
                               group
                               bg-white
                               dark:bg-gray-900
                               border
                               border-gray-200
                               dark:border-gray-800
                               rounded-xl
                               p-6
                               hover:border-blue-400
                               dark:hover:border-blue-700
                               hover:shadow-md
                               focus:outline-none
                               focus:ring-2
                               focus:ring-blue-600
                               focus:ring-offset-2
                               dark:focus:ring-offset-gray-950">


                        <div
                            class="flex
                                   items-center
                                   justify-between">


                            <div
                                class="h-11
                                       w-11
                                       rounded-lg
                                       bg-blue-50
                                       text-blue-700
                                       dark:bg-blue-950/40
                                       dark:text-blue-400
                                       flex
                                       items-center
                                       justify-center">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    aria-hidden="true">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M5.121 17.804A9 9 0 1112
                                           21a9 9 0 01-6.879-3.196z"
                                    />

                                </svg>

                            </div>


                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5
                                       text-gray-300
                                       group-hover:text-blue-600
                                       dark:group-hover:text-blue-400
                                       transition"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                aria-hidden="true">

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M7 17L17 7M7 7h10v10"
                                />

                            </svg>

                        </div>


                        <h3
                            class="mt-5
                                   text-lg
                                   font-semibold
                                   text-gray-900
                                   dark:text-white">

                            Legacy Administration

                        </h3>


                        <p
                            class="mt-1
                                   text-sm
                                   leading-6
                                   text-gray-500
                                   dark:text-gray-400">

                            Access the legacy administration system.

                        </p>

                    </a>



                    <!-- ================================================= -->
                    <!-- EBURSARY -->
                    <!-- ================================================= -->

                    <a
                        href="/login"
                        class="system-card
                               group
                               bg-white
                               dark:bg-gray-900
                               border
                               border-gray-200
                               dark:border-gray-800
                               rounded-xl
                               p-6
                               hover:border-amber-400
                               dark:hover:border-amber-700
                               hover:shadow-md
                               focus:outline-none
                               focus:ring-2
                               focus:ring-amber-500
                               focus:ring-offset-2
                               dark:focus:ring-offset-gray-950">


                        <div
                            class="flex
                                   items-center
                                   justify-between">


                            <div
                                class="h-11
                                       w-11
                                       rounded-lg
                                       bg-amber-50
                                       text-amber-700
                                       dark:bg-amber-950/40
                                       dark:text-amber-400
                                       flex
                                       items-center
                                       justify-center">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    aria-hidden="true">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M12 8c-1.657 0-3
                                           .895-3 2s1.343 2 3
                                           2 3 .895 3 2-1.343
                                           2-3 2m0-8c1.657 0
                                           3-.895 3-2s-1.343-2-3-2
                                           -3 .895-3 2 1.343 2 3 2z"
                                    />

                                </svg>

                            </div>


                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5
                                       text-gray-300
                                       group-hover:text-amber-600
                                       dark:group-hover:text-amber-400
                                       transition"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                aria-hidden="true">

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M7 17L17 7M7 7h10v10"
                                />

                            </svg>

                        </div>


                        <h3
                            class="mt-5
                                   text-lg
                                   font-semibold
                                   text-gray-900
                                   dark:text-white">

                            eBursary

                        </h3>


                        <p
                            class="mt-1
                                   text-sm
                                   leading-6
                                   text-gray-500
                                   dark:text-gray-400">

                            Finance, fees and payment management.

                        </p>

                    </a>



                    <!-- ================================================= -->
                    <!-- REPORTS -->
                    <!-- ================================================= -->

                    <a
                        href="/login"
                        class="system-card
                               group
                               bg-white
                               dark:bg-gray-900
                               border
                               border-gray-200
                               dark:border-gray-800
                               rounded-xl
                               p-6
                               hover:border-purple-400
                               dark:hover:border-purple-700
                               hover:shadow-md
                               focus:outline-none
                               focus:ring-2
                               focus:ring-purple-600
                               focus:ring-offset-2
                               dark:focus:ring-offset-gray-950">


                        <div
                            class="flex
                                   items-center
                                   justify-between">


                            <div
                                class="h-11
                                       w-11
                                       rounded-lg
                                       bg-purple-50
                                       text-purple-700
                                       dark:bg-purple-950/40
                                       dark:text-purple-400
                                       flex
                                       items-center
                                       justify-center">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    aria-hidden="true">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M9 17v-6m4 6v-4m4
                                           4v-2M5 21h14a2 2 0
                                           002-2V5a2 2 0 00-2-2H5
                                           a2 2 0 00-2 2v14a2 2
                                           0 002 2z"
                                    />

                                </svg>

                            </div>


                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5
                                       text-gray-300
                                       group-hover:text-purple-600
                                       dark:group-hover:text-purple-400
                                       transition"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                aria-hidden="true">

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M7 17L17 7M7 7h10v10"
                                />

                            </svg>

                        </div>


                        <h3
                            class="mt-5
                                   text-lg
                                   font-semibold
                                   text-gray-900
                                   dark:text-white">

                            Reports &amp; Analytics

                        </h3>


                        <p
                            class="mt-1
                                   text-sm
                                   leading-6
                                   text-gray-500
                                   dark:text-gray-400">

                            Generate academic and administrative reports.

                        </p>

                    </a>



                    <!-- ================================================= -->
                    <!-- SETTINGS -->
                    <!-- ================================================= -->

                    <a
                        href="/login"
                        class="system-card
                               group
                               bg-white
                               dark:bg-gray-900
                               border
                               border-gray-200
                               dark:border-gray-800
                               rounded-xl
                               p-6
                               hover:border-gray-400
                               dark:hover:border-gray-600
                               hover:shadow-md
                               focus:outline-none
                               focus:ring-2
                               focus:ring-gray-500
                               focus:ring-offset-2
                               dark:focus:ring-offset-gray-950">


                        <div
                            class="flex
                                   items-center
                                   justify-between">


                            <div
                                class="h-11
                                       w-11
                                       rounded-lg
                                       bg-gray-100
                                       text-gray-700
                                       dark:bg-gray-800
                                       dark:text-gray-300
                                       flex
                                       items-center
                                       justify-center">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    aria-hidden="true">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M10.325 4.317a1.724
                                           1.724 0 013.35 0
                                           1.724 1.724 0 002.573
                                           1.066 1.724 1.724 0
                                           012.897 2.897 1.724
                                           1.724 0 001.066 2.573
                                           1.724 1.724 0 010 3.35
                                           1.724 1.724 0 00-1.066
                                           2.573 1.724 1.724 0 01-2.897
                                           2.897 1.724 1.724 0 00-2.573
                                           1.066 1.724 1.724 0 01-3.35
                                           0 1.724 1.724 0 00-2.573-1.066
                                           1.724 1.724 0 01-2.897-2.897
                                           1.724 1.724 0 00-1.066-2.573
                                           1.724 1.724 0 010-3.35
                                           1.724 1.724 0 001.066-2.573
                                           1.724 1.724 0 012.897-2.897
                                           1.724 1.724 0 002.573-1.066z"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M15 12a3 3 0 11-6 0
                                           3 3 0 016 0z"
                                    />

                                </svg>

                            </div>


                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5
                                       text-gray-300
                                       group-hover:text-gray-600
                                       dark:group-hover:text-gray-300
                                       transition"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                aria-hidden="true">

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M7 17L17 7M7 7h10v10"
                                />

                            </svg>

                        </div>


                        <h3
                            class="mt-5
                                   text-lg
                                   font-semibold
                                   text-gray-900
                                   dark:text-white">

                            System Settings

                        </h3>


                        <p
                            class="mt-1
                                   text-sm
                                   leading-6
                                   text-gray-500
                                   dark:text-gray-400">

                            Configure system and academic settings.

                        </p>

                    </a>



                    <!-- ================================================= -->
                    <!-- ADMIN MANAGEMENT -->
                    <!-- ================================================= -->

                    <a
                        href="/login"
                        class="system-card
                               group
                               bg-white
                               dark:bg-gray-900
                               border
                               border-gray-200
                               dark:border-gray-800
                               rounded-xl
                               p-6
                               hover:border-red-400
                               dark:hover:border-red-700
                               hover:shadow-md
                               focus:outline-none
                               focus:ring-2
                               focus:ring-red-600
                               focus:ring-offset-2
                               dark:focus:ring-offset-gray-950">


                        <div
                            class="flex
                                   items-center
                                   justify-between">


                            <div
                                class="h-11
                                       w-11
                                       rounded-lg
                                       bg-red-50
                                       text-red-700
                                       dark:bg-red-950/40
                                       dark:text-red-400
                                       flex
                                       items-center
                                       justify-center">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    aria-hidden="true">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M17 20h5v-2a3 3 0
                                           00-5.356-1.857M17 20H7
                                           m10 0v-2c0-.656-.126-1.283
                                           -.356-1.857M7 20H2v-2a3
                                           3 0 015.356-1.857M7 20v-2
                                           c0-.656.126-1.283.356-1.857
                                           m0 0a5.002 5.002 0 019.288
                                           0M15 7a3 3 0 11-6 0 3 3
                                           0 016 0z"
                                    />

                                </svg>

                            </div>


                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5
                                       text-gray-300
                                       group-hover:text-red-600
                                       dark:group-hover:text-red-400
                                       transition"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                aria-hidden="true">

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M7 17L17 7M7 7h10v10"
                                />

                            </svg>

                        </div>


                        <h3
                            class="mt-5
                                   text-lg
                                   font-semibold
                                   text-gray-900
                                   dark:text-white">

                            Administrator Management

                        </h3>


                        <p
                            class="mt-1
                                   text-sm
                                   leading-6
                                   text-gray-500
                                   dark:text-gray-400">

                            Manage administrators, roles and permissions.

                        </p>

                    </a>

                </div>

            </section>

        </div>

    </main>



    <!-- ========================================================= -->
    <!-- FOOTER -->
    <!-- ========================================================= -->

    <footer
        class="border-t
               border-gray-200
               bg-white
               dark:border-gray-800
               dark:bg-gray-950">

        <div
            class="max-w-7xl
                   mx-auto
                   px-4
                   sm:px-6">

            <div
                class="min-h-16
                       flex
                       flex-col
                       sm:flex-row
                       items-center
                       justify-between
                       gap-2">

                <p
                    class="text-xs
                           text-gray-500
                           dark:text-gray-500">

                    &copy;
                    <span id="year"></span>
                    Heritage Polytechnic

                </p>


                <p
                    class="text-xs
                           text-gray-500
                           dark:text-gray-500">

                    Central Administration Portal

                </p>

            </div>

        </div>

    </footer>

</div>



<!-- ============================================================= -->
<!-- JAVASCRIPT -->
<!-- ============================================================= -->

<script>

    (() => {

        'use strict';


        /*
        |--------------------------------------------------------------------------
        | Elements
        |--------------------------------------------------------------------------
        */

        const html = document.documentElement;

        const themeToggle =
            document.getElementById('themeToggle');

        const moonIcon =
            document.getElementById('moonIcon');

        const sunIcon =
            document.getElementById('sunIcon');

        const greeting =
            document.getElementById('greeting');

        const currentDate =
            document.getElementById('currentDate');

        const year =
            document.getElementById('year');


        /*
        |--------------------------------------------------------------------------
        | Year
        |--------------------------------------------------------------------------
        */

        year.textContent =
            new Date().getFullYear();


        /*
        |--------------------------------------------------------------------------
        | Greeting
        |--------------------------------------------------------------------------
        */

        const hour =
            new Date().getHours();

        if (hour < 12) {

            greeting.textContent =
                'Good morning, Admin';

        } else if (hour < 18) {

            greeting.textContent =
                'Good afternoon, Admin';

        } else {

            greeting.textContent =
                'Good evening, Admin';

        }


        /*
        |--------------------------------------------------------------------------
        | Current Date
        |--------------------------------------------------------------------------
        */

        const today =
            new Date();

        currentDate.textContent =
            new Intl.DateTimeFormat('en-NG', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }).format(today);


        /*
        |--------------------------------------------------------------------------
        | Theme
        |--------------------------------------------------------------------------
        */

        function setTheme(theme) {

            const isDark =
                theme === 'dark';

            html.classList.toggle(
                'dark',
                isDark
            );

            moonIcon.classList.toggle(
                'hidden',
                isDark
            );

            sunIcon.classList.toggle(
                'hidden',
                !isDark
            );

            themeToggle.setAttribute(
                'aria-pressed',
                String(isDark)
            );

            themeToggle.setAttribute(
                'aria-label',
                isDark
                    ? 'Switch to light mode'
                    : 'Switch to dark mode'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Initial Theme
        |--------------------------------------------------------------------------
        */

        const savedTheme =
            localStorage.getItem('theme');


        if (
            savedTheme === 'dark' ||
            savedTheme === 'light'
        ) {

            setTheme(savedTheme);

        } else {

            const prefersDark =
                window.matchMedia &&
                window.matchMedia(
                    '(prefers-color-scheme: dark)'
                ).matches;

            setTheme(
                prefersDark
                    ? 'dark'
                    : 'light'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Theme Toggle
        |--------------------------------------------------------------------------
        */

        themeToggle.addEventListener(
            'click',
            () => {

                const newTheme =
                    html.classList.contains('dark')
                        ? 'light'
                        : 'dark';

                localStorage.setItem(
                    'theme',
                    newTheme
                );

                setTheme(newTheme);

            }
        );

    })();

</script>

</body>
</html>
