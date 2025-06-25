<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>e-Justice Registration</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @keyframes slideFade {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .animate-slide-fade {
      animation: slideFade 0.6s ease-out both;
    }
    body {
      background-image: url('https://images.unsplash.com/photo-1570129477492-45c003edd2be?auto=format&fit=crop&w=1600&q=80'); /* same justice photo */
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-black bg-opacity-50 relative">

  <!-- Overlay tint -->
  <div class="absolute inset-0 bg-black bg-opacity-60"></div>

  <!-- Registration Form -->
  <div class="relative z-10 w-full max-w-md p-8 bg-white/10 backdrop-blur-md border border-white/30 rounded-2xl shadow-xl animate-slide-fade">
    <h2 class="text-3xl font-bold text-white text-center mb-6">📝 Create Your Account</h2>

    <?php if (!empty($error)): ?>
      <div class="bg-red-100 text-red-800 text-sm px-4 py-2 rounded mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($msg)): ?>
      <div class="bg-green-100 text-green-800 text-sm px-4 py-2 rounded mb-4"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <input name="fname" placeholder="First Name"
             class="w-full px-4 py-2 rounded bg-white/80 text-black border border-gray-300 placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
             required>

      <input name="lname" placeholder="Last Name"
             class="w-full px-4 py-2 rounded bg-white/80 text-black border border-gray-300 placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
             required>

      <input name="email" type="email" placeholder="Email"
             class="w-full px-4 py-2 rounded bg-white/80 text-black border border-gray-300 placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
             required>

      <input type="password" name="password" placeholder="Password"
             class="w-full px-4 py-2 rounded bg-white/80 text-black border border-gray-300 placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
             required>

      <button type="submit"
              class="w-full py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded transition">
        Sign Up
      </button>
    </form>

    <div class="text-sm text-gray-300 text-center mt-4">
      Already have an account?
      <a href="login.php" class="underline hover:text-white ml-1">Back to Login</a>
    </div>
  </div>

</body>
</html>
