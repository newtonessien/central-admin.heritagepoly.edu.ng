<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HERITAGE POLYTECHNIC - Central Admin Portal</title>
  <link rel="icon" type="image/x-icon" href="/logo/icon.png">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' }
  </script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 font-sans text-gray-800 dark:text-gray-100 transition-colors duration-500">

<!-- Header -->
<header class="bg-green-700 dark:bg-green-800 shadow transition-colors duration-500">
  <div class="max-w-6xl mx-auto px-3 py-2 flex items-center justify-between">
    <div class="flex items-center space-x-2">
      <img src="/logo/icon.png" alt="logo" class="h-10 w-10">
      <h1 class="text-xl font-bold text-white">HERITAGEPOLY Central Admin</h1>
    </div>
    <div class="flex items-center gap-3">
      <a href="/login"
         class="inline-block bg-white text-green-700 px-3 py-2 rounded font-semibold hover:bg-gray-100 transition">
        🔑 Go to Login
      </a>
      <button id="themeToggle"
              class="px-3 py-2 rounded bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition">
        🌙 Dark Mode
      </button>
    </div>
  </div>
</header>

<!-- Greeting -->
<section class="max-w-6xl mx-auto px-6 py-8 text-center">
  <h2 id="greeting" class="text-2xl font-bold">Welcome, Admin 👋</h2>
  <p class="text-gray-600 dark:text-gray-400 mt-2">
    One secure control system for managing Admissions, Students, ebursary, Reports, and Settings — all in one place.
  </p>
</section>

<!-- Quick Links -->
<main class="max-w-6xl mx-auto px-6 pb-12">
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">

    <!-- Admissions -->
    <a href="/login" class="group bg-white dark:bg-gray-800 shadow rounded-xl p-8 flex flex-col items-center text-center hover:shadow-lg hover:-translate-y-1 transition">
      <div class="bg-green-100 text-green-700 p-4 rounded-full mb-4 group-hover:bg-green-600 group-hover:text-white transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v7m0-7L3 9m9 5l9-5" />
        </svg>
      </div>
      <h4 class="text-lg font-semibold">Admissions</h4>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Manage admission processes</p>
    </a>

    <!-- Student Portal -->
    <a href="/login" class="group bg-white dark:bg-gray-800 shadow rounded-xl p-8 flex flex-col items-center text-center hover:shadow-lg hover:-translate-y-1 transition">
      <div class="bg-green-100 text-green-700 p-4 rounded-full mb-4 group-hover:bg-green-600 group-hover:text-white transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1112 21a9 9 0 01-6.879-3.196z" />
        </svg>
      </div>
      <h4 class="text-lg font-semibold">Student Portal</h4>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Control student data</p>
    </a>

    <!-- Ebursary -->
    <a href="/login" class="group bg-white dark:bg-gray-800 shadow rounded-xl p-8 flex flex-col items-center text-center hover:shadow-lg hover:-translate-y-1 transition">
      <div class="bg-green-100 text-green-700 p-4 rounded-full mb-4 group-hover:bg-green-600 group-hover:text-white transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.657 0 3-.895 3-2s-1.343-2-3-2-3 .895-3 2 1.343 2 3 2z" />
        </svg>
      </div>
      <h4 class="text-lg font-semibold">ebursary</h4>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Finance & payments</p>
    </a>

    <!-- Reports -->
    <a href="/login" class="group bg-white dark:bg-gray-800 shadow rounded-xl p-8 flex flex-col items-center text-center hover:shadow-lg hover:-translate-y-1 transition">
      <div class="bg-green-100 text-green-700 p-4 rounded-full mb-4 group-hover:bg-green-600 group-hover:text-white transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m4 6v-4m4 4v-2M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
      </div>
      <h4 class="text-lg font-semibold">Reports</h4>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Generate analytics</p>
    </a>

    <!-- Settings -->
    <a href="/login" class="group bg-white dark:bg-gray-800 shadow rounded-xl p-8 flex flex-col items-center text-center hover:shadow-lg hover:-translate-y-1 transition">
      <div class="bg-green-100 text-green-700 p-4 rounded-full mb-4 group-hover:bg-green-600 group-hover:text-white transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.983 2.021a1 1 0 011.034 0l1.342.774a1 1 0 01.45.902v1.548a7.96 7.96 0 012.01 1.165l1.463-.365a1 1 0 011.19.591l.774 1.342a1 1 0 01-.452 1.33l-1.262.727c.087.324.151.658.188 1.002h1.548a1 1 0 01.902.45l.774 1.342a1 1 0 01-.59 1.19l-1.464.365a7.96 7.96 0 01-1.165 2.01l.365 1.463a1 1 0 01-.59 1.19l-1.342.774z" />
        </svg>
      </div>
      <h4 class="text-lg font-semibold">Settings</h4>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">System configurations</p>
    </a>

    <!-- Admin Management -->
    <a href="/login" class="group bg-white dark:bg-gray-800 shadow rounded-xl p-8 flex flex-col items-center text-center hover:shadow-lg hover:-translate-y-1 transition">
      <div class="bg-green-100 text-green-700 p-4 rounded-full mb-4 group-hover:bg-green-600 group-hover:text-white transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0-1.105-.895-2-2-2s-2 .895-2 2 .895 2 2 2 2-.895 2-2-.895-2-2-2-2 .895-2 2zm0 0v4m-4 4h8m-4-4v4" />
        </svg>
      </div>
      <h4 class="text-lg font-semibold">Admin Management</h4>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Roles & permissions</p>
    </a>

  </div>
</main>

<!-- Footer -->
<footer class="bg-gray-800 py-4 mt-10">
  <div class="text-center text-gray-400 text-sm">
    © <span id="year"></span> Heritage Polytechnic — Central Admin Portal
  </div>
</footer>

<script>
  // Year
  document.getElementById("year").textContent = new Date().getFullYear();

  // Theme toggle
  const htmlEl = document.documentElement;
  const toggleBtn = document.getElementById('themeToggle');

  if (localStorage.getItem('theme') === 'dark') {
    htmlEl.classList.add('dark');
    toggleBtn.textContent = "☀️ Light Mode";
  } else {
    toggleBtn.textContent = "🌙 Dark Mode";
  }

  toggleBtn.addEventListener('click', () => {
    htmlEl.classList.toggle('dark');
    if (htmlEl.classList.contains('dark')) {
      localStorage.setItem('theme', 'dark');
      toggleBtn.textContent = "☀️ Light Mode";
    } else {
      localStorage.setItem('theme', 'light');
      toggleBtn.textContent = "🌙 Dark Mode";
    }
  });

  // Dynamic greeting
  const greeting = document.getElementById('greeting');
  const hour = new Date().getHours();
  let message = "Welcome, Admin 👋";
  if (hour < 12) {
    message = "Good morning, Admin 🌅";
  } else if (hour < 18) {
    message = "Good afternoon, Admin 🌞";
  } else {
    message = "Good evening, Admin 🌙";
  }
  greeting.textContent = message;
</script>

</body>
</html>
