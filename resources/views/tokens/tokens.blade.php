<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Token Generator</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    .glass {
      background: rgba(255, 255, 255, 0.05);
      border-radius: 20px;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.45);
    }
    .token-number {
      font-size: 3rem;
      font-weight: 800;
      letter-spacing: 0.1em;
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-[#111827] px-4">

  <!-- Main Card -->
  <div class="glass w-full max-w-md p-8 text-center text-white">
    <h1 class="text-3xl font-bold mb-6">🎫 Name Generator</h1>

    <!-- Gender Toggle -->
    <p class="text-white font-semibold mb-2">Gender Select</p>
    <div class="flex justify-center gap-6 mb-6">
      <!-- Mr. -->
      <div class="flex items-center space-x-3">
        <button id="toggleMr"
          class="w-16 h-8 bg-white/20 rounded-full flex items-center p-1 transition-all duration-300">
          <div id="dotMr"
            class="w-6 h-6 bg-white rounded-full shadow-md transition-all duration-300">
          </div>
        </button>
        <span class="text-white font-medium">Mr.</span>
      </div>

      <!-- Mrs. -->
      <div class="flex items-center space-x-3">
        <button id="toggleMrs"
          class="w-16 h-8 bg-white/20 rounded-full flex items-center p-1 transition-all duration-300">
          <div id="dotMrs"
            class="w-6 h-6 bg-white rounded-full shadow-md transition-all duration-300">
          </div>
        </button>
        <span class="text-white font-medium">Mrs.</span>
      </div>
    </div>

    <!-- Output -->
    <p class="text-gray-300">Your Generated Name</p>
    <div id="generatedToken" class="token-number mt-4"></div>
    <p id="dateTime" class="mt-3 text-sm text-gray-400"></p>

    <!-- Error -->
    <div id="errorMessage" class="hidden mt-4 text-red-400 font-semibold"></div>

    <!-- Input -->
    <input style="margin-top: 10px;"
      type="text"
      id="customerName"
      placeholder="Enter your name"
      class="w-full px-4 py-2 rounded-full bg-[#111827] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-white mb-4"
    />

    <!-- Button -->
    <button
      id="generateTokenBtn"
      class="mt-2 bg-gradient-to-r from-[#111827] to-gray-700 hover:from-gray-800 hover:to-gray-800 text-white font-bold py-3 px-6 rounded-full w-full transition-all duration-300"
    >
      Let's Go
    </button>

    <p class="mt-6 text-xs italic">Please wait for your name to appear.</p>
  </div>

  <!-- Script -->
  <script>
    const btn = document.getElementById('generateTokenBtn');
    const nameInput = document.getElementById('customerName');
    const tokenDiv = document.getElementById('generatedToken');
    const dateTimeDiv = document.getElementById('dateTime');
    const errorMessage = document.getElementById('errorMessage');

    const toggleMr = document.getElementById("toggleMr");
    const toggleMrs = document.getElementById("toggleMrs");
    const dotMr = document.getElementById("dotMr");
    const dotMrs = document.getElementById("dotMrs");

    let selectedPrefix = "";

    toggleMr.addEventListener("click", () => {
      selectedPrefix = "Mr.";

      toggleMr.style.backgroundColor = "#ffffff";
      dotMr.style.backgroundColor = "#111827";
      dotMr.style.transform = "translateX(32px)";

      toggleMrs.style.backgroundColor = "rgba(255,255,255,0.1)";
      dotMrs.style.backgroundColor = "#ffffff";
      dotMrs.style.transform = "translateX(0)";

      // Auto-fill prefix in input
      const nameOnly = nameInput.value.replace(/^Mr\.|^Mrs\./i, "").trim();
      nameInput.value = `Mr. ${nameOnly}`;
    });

    toggleMrs.addEventListener("click", () => {
      selectedPrefix = "Mrs.";

      toggleMrs.style.backgroundColor = "#ffffff";
      dotMrs.style.backgroundColor = "#111827";
      dotMrs.style.transform = "translateX(32px)";

      toggleMr.style.backgroundColor = "rgba(255,255,255,0.1)";
      dotMr.style.backgroundColor = "#ffffff";
      dotMr.style.transform = "translateX(0)";

      // Auto-fill prefix in input
      const nameOnly = nameInput.value.replace(/^Mr\.|^Mrs\./i, "").trim();
      nameInput.value = `Mrs. ${nameOnly}`;
    });

    btn.addEventListener('click', () => {
      const fullName = nameInput.value.trim();

      if (!fullName || !selectedPrefix) {
        Swal.fire({
          icon: 'warning',
          title: 'Enter name & select gender',
          background: '#111827',
          color: '#fff'
        });
        return;
      }

      Swal.fire({
        title: 'Processing...',
        background: '#111827',
        color: '#fff',
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });

      fetch('/tokens/generate', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ customer_name: fullName })
      })
      .then(async res => {
        Swal.close();
        const data = await res.json();

        if (!res.ok) {
          errorMessage.textContent = data.message || 'Failed to generate token. Try again.';
          errorMessage.classList.remove('hidden');
          tokenDiv.textContent = '';
          dateTimeDiv.textContent = '';
          setTimeout(() => errorMessage.classList.add('hidden'), 4000);
          return;
        }

        tokenDiv.textContent = data.token.customer_name.toUpperCase();
        const now = new Date();
        dateTimeDiv.textContent = `📅 ${now.toLocaleDateString()} | 🕒 ${now.toLocaleTimeString()}`;
        nameInput.value = '';
      })
      .catch(() => {
        Swal.close();
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Something went wrong. Try again later!',
          background: '#111827',
          color: '#fff'
        });
      });
    });
  </script>
</body>
</html>
