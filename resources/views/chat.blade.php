<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>AI Chat - {{ config('app.name', 'Smart Wallet') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Theme initialization script to prevent FOUC (Flash of Unstyled Content) -->
    <script>
      if (
        localStorage.theme === 'dark' ||
        (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
      ) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
      *, ::before, ::after {
        box-sizing: border-box;
      }
      body {
        margin: 0;
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        -webkit-font-smoothing: antialiased;
      }
      /* Custom scrollbar for chat */
      .chat-scroll::-webkit-scrollbar {
        width: 5px;
        height: 5px;
      }
      .chat-scroll::-webkit-scrollbar-track {
        background: transparent;
      }
      .chat-scroll::-webkit-scrollbar-thumb {
        background-color: #E5E7EB;
        border-radius: 9999px;
      }
      .chat-scroll::-webkit-scrollbar-thumb:hover {
        background-color: #D1D5DB;
      }
      .dark .chat-scroll::-webkit-scrollbar-thumb {
        background-color: #262626;
      }
      .dark .chat-scroll::-webkit-scrollbar-thumb:hover {
        background-color: #333333;
      }

      /* Animasi Kemakmuran - Fluid & Stable Micro-interactions */
      @keyframes fadeInUp {
        from {
          opacity: 0;
          transform: translateY(8px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
      .animate-fade-in {
        animation: fadeInUp 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
      }

      /* Touch Optimizations */
      button, a, input, [role="button"] {
        -webkit-tap-highlight-color: transparent;
        touch-action: manipulation;
      }
      .chat-scroll {
        -webkit-overflow-scrolling: touch;
        overscroll-behavior-y: contain;
      }
    </style>
  </head>
  <body class="h-[100dvh] min-h-[100dvh] max-h-[100dvh] w-full bg-[#FFFFFF] dark:bg-[#0A0A0A] text-[#111111] dark:text-[#ECECEC] overflow-hidden flex flex-col antialiased transition-colors duration-200">
    <!-- Header -->
    <header
      data-pencil-name="Header"
      class="relative z-40 w-full h-[60px] sm:h-[64px] shrink-0 flex flex-row gap-2 sm:gap-4 px-3 sm:px-6 justify-between items-center bg-[#FFFFFF]/95 dark:bg-[#0A0A0A]/95 backdrop-blur-md border-b border-[#E5E7EB] dark:border-[#262626] transition-colors duration-200 select-none"
    >
      <div class="flex items-center gap-2 sm:gap-3 min-w-0">
        <!-- Mobile Sidebar Toggle -->
        <button
          id="mobile-sidebar-toggle"
          type="button"
          class="md:hidden flex items-center justify-center w-9 h-9 rounded-xl text-[#6B7280] dark:text-[#B4B4B4] hover:text-[#111111] dark:hover:text-[#ECECEC] hover:bg-neutral-100 dark:hover:bg-[#1A1A1A] active:scale-95 transition-all cursor-pointer"
          aria-label="Toggle sidebar"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
          </svg>
        </button>

        <!-- Logo -->
        <div
          data-pencil-name="Logo"
          class="flex flex-row gap-2 sm:gap-[10px] items-center cursor-pointer select-none shrink-0"
          onclick="startNewChat()"
        >
          <div
            data-pencil-name="Logo Dot"
            class="w-[26px] h-[26px] sm:w-[28px] sm:h-[28px] shrink-0 bg-[#111111] dark:bg-[#ECECEC] rounded-full flex items-center justify-center transition-colors duration-200"
          >
            <div class="w-2 h-2 bg-white dark:bg-[#0A0A0A] rounded-full opacity-90"></div>
          </div>
          <div
            data-pencil-name="App Title"
            class="text-[15px] sm:text-[16px] leading-normal text-[#111111] dark:text-[#ECECEC] font-semibold text-left whitespace-nowrap tracking-tight transition-colors duration-200"
          >
            AI Chat
          </div>
        </div>

        <!-- Model Dropdown (Desktop Only) -->
        <div class="relative hidden md:block">
          <button
            id="model-dropdown-button"
            type="button"
            data-pencil-name="Model Dropdown"
            class="flex flex-row gap-1.5 sm:gap-[8px] px-2 sm:px-[12px] py-1.5 sm:py-[8px] items-center bg-[#F7F7F8] dark:bg-[#1A1A1A] border border-[#E5E7EB] dark:border-[#262626] rounded-xl hover:bg-neutral-100 dark:hover:bg-[#222222] active:scale-95 transition-all duration-200 cursor-pointer select-none max-w-[130px] xs:max-w-[170px] sm:max-w-none"
          >
            <span
              id="model-display-name"
              data-pencil-name="Model Name"
              class="text-xs sm:text-[14px] leading-normal text-[#111111] dark:text-[#ECECEC] font-semibold text-left truncate"
            >
              GPT-4o
            </span>
            <span
              id="model-provider-badge"
              data-pencil-name="Model Sub"
              class="hidden md:inline text-[11px] leading-normal px-2 py-0.5 rounded-md bg-neutral-200 dark:bg-[#2A2A2A] text-[#4B5563] dark:text-[#D1D5DB] font-medium text-left whitespace-nowrap"
            >
              Moonshot
            </span>
            <svg
              data-pencil-name="Chevron"
              viewBox="0 0 14 14"
              class="w-[14px] h-[14px] sm:w-[16px] sm:h-[16px] shrink-0 transition-transform duration-200 text-[#6B7280] dark:text-[#B4B4B4]"
              id="model-dropdown-arrow"
            >
              <path
                d="M3.34619 4.68945q-0.25293 0.08545-0.37939 0.30762-0.04102 0.08545-0.04102 0.25293 0 0.16748 0.04785 0.25977 0.05127 0.08887 1.8628 1.9038 1.81494 1.81152 1.9038 1.8628 0.09229 0.04785 0.25977 0.04785 0.16748 0 0.25635-0.04785 0.09229-0.05127 1.90381-1.8628 1.81494-1.81494 1.86279-1.9038 0.05127-0.09229 0.05127-0.25977 0-0.16748-0.04102-0.25293-0.09912-0.16748-0.28027-0.2666-0.05811-0.02734-0.09912-0.04102-0.04102-0.01367-0.15381-0.01367-0.11279 0-0.15381 0.01367-0.04101 0.01367-0.11279 0.04102-0.09912 0.05811-1.66455 1.62695l-1.56885 1.56543-2.82666-2.81299q-0.31104-0.29395-0.42041-0.37939-0.08545-0.05469-0.19824-0.05469l-0.02735 0q-0.14014 0-0.18115 0.01367z"
                fill="currentColor"
              ></path>
            </svg>
          </button>

          <!-- Dropdown Menu -->
          <div
            id="model-dropdown-menu"
            class="hidden fixed sm:absolute left-2 right-2 sm:left-0 sm:right-auto top-[62px] sm:top-full mt-1.5 sm:w-80 max-h-[75vh] bg-white dark:bg-[#1A1A1A] rounded-2xl shadow-2xl border border-[#E5E7EB] dark:border-[#262626] py-1 z-50 text-left flex flex-col overflow-hidden backdrop-blur-xl animate-fade-in"
          >
            <div class="p-2.5 border-b border-[#E5E7EB] dark:border-[#262626] bg-[#FAFAFA] dark:bg-[#141414]">
              <div class="relative">
                <input
                  type="text"
                  id="model-search-input"
                  placeholder="Cari model Tokenportal..."
                  class="w-full text-xs px-2.5 py-2 pl-7 rounded-lg bg-white dark:bg-[#202020] text-[#111111] dark:text-[#ECECEC] outline-none border border-[#E5E7EB] dark:border-[#333333] focus:border-neutral-400"
                />
                <svg class="w-3.5 h-3.5 text-[#9CA3AF] absolute left-2 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
              </div>
              <div class="flex items-center justify-between mt-1.5 px-1 text-[11px] text-[#6B7280] dark:text-[#9CA3AF]">
                <span>Provider: <strong class="text-[#111111] dark:text-[#ECECEC]">Tokenportal.id</strong></span>
                <span id="model-count-label">Memuat...</span>
              </div>
            </div>
            <div id="model-dropdown-list" class="max-h-72 overflow-y-auto divide-y divide-[#F3F4F6] dark:divide-[#262626]">
              <div class="p-3 text-center text-xs text-[#6B7280] dark:text-[#9CA3AF]">
                Memuat model Tokenportal...
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Header Actions -->
      <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
        <!-- Agent Toggle (Desktop Only) -->
        <button
          id="agent-toggle-btn"
          type="button"
          onclick="toggleAgentMode()"
          data-pencil-name="Agent Toggle"
          class="hidden md:flex flex-row gap-1.5 sm:gap-[8px] px-2 sm:px-[12px] py-1.5 sm:py-[6px] items-center bg-[#F7F7F8] dark:bg-[#1A1A1A] border border-[#E5E7EB] dark:border-[#262626] rounded-full hover:bg-neutral-100 dark:hover:bg-[#222222] active:scale-95 transition-all duration-200 cursor-pointer select-none"
          title="Agent Mode toggle"
        >
          <svg
            class="w-[14px] h-[14px] sm:w-[16px] sm:h-[16px] shrink-0 text-[#6B7280] dark:text-[#B4B4B4]"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-12h9v9h-9v-9Z" />
          </svg>
          <span
            data-pencil-name="Agent Label"
            class="hidden sm:inline text-[13px] leading-normal text-[#6B7280] dark:text-[#B4B4B4] font-normal text-left whitespace-nowrap"
          >
            agent
          </span>
          <div
            id="agent-toggle-track"
            data-pencil-name="Agent Toggle Track"
            class="w-[32px] sm:w-[36px] h-[18px] sm:h-[20px] shrink-0 flex flex-row p-[2px] justify-start items-center bg-neutral-300 dark:bg-[#333333] rounded-[10px] transition-colors duration-200"
          >
            <div
              id="agent-toggle-knob"
              data-pencil-name="Agent Toggle Knob"
              class="w-[14px] sm:w-[16px] h-[14px] sm:h-[16px] bg-[#FFFFFF] dark:bg-[#B4B4B4] rounded-full transition-transform duration-200"
            ></div>
          </div>
        </button>

        <!-- Search Toggle (Desktop Only) -->
        <button
          id="search-toggle-btn"
          type="button"
          onclick="toggleSearchMode()"
          data-pencil-name="Search Toggle"
          class="hidden md:flex flex-row gap-1.5 sm:gap-[8px] px-2 sm:px-[12px] py-1.5 sm:py-[6px] items-center bg-[#F7F7F8] dark:bg-[#1A1A1A] border border-[#E5E7EB] dark:border-[#262626] rounded-full hover:bg-neutral-100 dark:hover:bg-[#222222] active:scale-95 transition-all duration-200 cursor-pointer select-none"
          title="Search toggle"
        >
          <svg
            data-pencil-name="Search Icon"
            viewBox="0 0 14 14"
            class="w-[14px] h-[14px] sm:w-[16px] sm:h-[16px] shrink-0 text-[#6B7280] dark:text-[#B4B4B4]"
          >
            <path
              d="M6.00537 1.17578q-0.04102 0.01367-0.23584 0.02734-0.57422 0.07178-1.16211 0.29053-0.58789 0.21533-1.09375 0.5503-0.39307 0.2666-0.74853 0.61181-0.35547 0.3418-0.62207 0.7041-0.646 0.92285-0.86817 2.03028-0.05811 0.28027-0.07861 0.46142-0.02051 0.18115-0.02051 0.57422 0 0.43408 0.02734 0.68018 0.02734 0.24268 0.12647 0.59472 0.19482 0.75537 0.60156 1.43555 0.40674 0.67676 0.96729 1.18262 0.40674 0.3623 0.81689 0.60156 0.41357 0.23926 0.91944 0.43408 1.06299 0.39307 2.21826 0.29395 1.15527-0.09912 2.1499-0.65967 0.12646-0.06836 0.33496-0.2085 0.2085-0.14014 0.29395-0.21191l0.05468-0.05469 1.10743 1.10742q1.12109 1.104 1.20312 1.15528 0.08545 0.04785 0.23926 0.04785 0.15381 0 0.23584-0.03418 0.08545-0.0376 0.18115-0.1333 0.09912-0.09912 0.1333-0.18115 0.0376-0.08545 0.0376-0.23926 0-0.15381-0.05127-0.23584-0.04785-0.08545-1.15186-1.20654l-1.10742-1.10743 0.05469-0.06836q0.05811-0.05469 0.19824-0.25293 0.82373-1.23047 0.89209-2.70703 0.07178-1.47656-0.63916-2.76513-0.5332-0.96729-1.42187-1.64405-0.88867-0.68018-1.95508-0.9331-0.29395-0.06836-0.54004-0.09571-0.24268-0.03076-0.60498-0.04443-0.43408 0-0.49219 0z m0.7417 1.17578q0.37939 0.01367 0.79297 0.14014 0.41357 0.12646 0.78955 0.32129 0.7417 0.39307 1.28857 1.08008 0.54687 0.68359 0.75538 1.49707 0.07178 0.28027 0.09228 0.49902 0.02051 0.21533 0.02051 0.50928 0 0.70068-0.19483 1.30224-0.39307 1.16211-1.33984 1.91065-0.94336 0.74853-2.16016 0.875-0.19824 0.01367-0.49218 0.00683-0.29395-0.00684-0.48877-0.03418-0.5332-0.08545-1.03907-0.30761-0.50244-0.22559-0.93652-0.56055-0.14014-0.12646-0.35205-0.3418-0.2085-0.21875-0.33154-0.37256-0.57422-0.76904-0.7417-1.69531-0.08545-0.43408-0.06494-0.90918 0.02051-0.4751 0.11963-0.89551 0.29395-1.14844 1.18945-1.95849 0.89551-0.81348 2.05762-1.02539 0.18115-0.02734 0.42041-0.04102 0.23926-0.01367 0.34863-0.01367l0.2666 0.01367z"
              fill="currentColor"
            ></path>
          </svg>
          <span
            data-pencil-name="Search Label"
            class="hidden sm:inline text-[13px] leading-normal text-[#6B7280] dark:text-[#B4B4B4] font-normal text-left whitespace-nowrap"
          >
            search
          </span>
          <div
            id="toggle-track"
            data-pencil-name="Toggle Track"
            class="w-[32px] sm:w-[36px] h-[18px] sm:h-[20px] shrink-0 flex flex-row p-[2px] justify-end items-center bg-[#111111] dark:bg-[#FFFFFF] rounded-[10px] transition-colors duration-200"
          >
            <div
              id="toggle-knob"
              data-pencil-name="Toggle Knob"
              class="w-[14px] sm:w-[16px] h-[14px] sm:h-[16px] bg-[#FFFFFF] dark:bg-[#0A0A0A] rounded-full transition-transform duration-200"
            ></div>
          </div>
        </button>

        <!-- Theme Toggle Button (Light/Dark Mode) -->
        <button
          id="theme-toggle-btn"
          type="button"
          onclick="toggleTheme()"
          class="flex flex-row gap-[6px] p-2 sm:px-[12px] sm:py-[8px] items-center bg-[#F7F7F8] dark:bg-[#1A1A1A] border border-[#E5E7EB] dark:border-[#262626] rounded-[10px] text-[#6B7280] dark:text-[#B4B4B4] hover:text-[#111111] dark:hover:text-[#ECECEC] hover:bg-neutral-100 dark:hover:bg-[#222222] active:scale-95 transition-all duration-200 cursor-pointer select-none"
          title="Toggle Light / Dark Mode"
          aria-label="Toggle theme"
        >
          <!-- Moon Icon (Visible in Light Mode) -->
          <svg id="theme-moon-icon" class="w-[16px] h-[16px] block dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
          </svg>
          <!-- Sun Icon (Visible in Dark Mode) -->
          <svg id="theme-sun-icon" class="w-[16px] h-[16px] hidden dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="5"></circle>
            <line x1="12" y1="1" x2="12" y2="3"></line>
            <line x1="12" y1="21" x2="12" y2="23"></line>
            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
            <line x1="1" y1="12" x2="3" y2="12"></line>
            <line x1="21" y1="12" x2="23" y2="12"></line>
            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
          </svg>
          <span class="hidden sm:inline text-[13px] leading-normal font-normal">
            <span class="dark:hidden">Dark</span>
            <span class="hidden dark:inline">Light</span>
          </span>
        </button>

        <!-- New Chat Button -->
        <button
          type="button"
          data-pencil-name="New Chat Button"
          onclick="startNewChat()"
          class="flex flex-row gap-[6px] px-2.5 sm:px-[14px] py-1.5 sm:py-[8px] items-center bg-[#111111] dark:bg-[#FFFFFF] hover:bg-neutral-800 dark:hover:bg-neutral-200 text-[#FFFFFF] dark:text-[#111111] rounded-[10px] active:scale-95 transition-all duration-200 cursor-pointer select-none shadow-xs"
        >
          <svg
            data-pencil-name="Plus Icon"
            viewBox="0 0 14 14"
            class="w-[14px] h-[14px] sm:w-[16px] sm:h-[16px] shrink-0 text-[#FFFFFF] dark:text-[#111111]"
          >
            <path
              d="M6.90088 2.35156q-0.0957 0.01367-0.17432 0.05127-0.0752 0.03418-0.15381 0.12647-0.0752 0.08887-0.11279 0.16748-0.03418 0.0752-0.03418 0.32812l0 3.40088-3.66748 0-0.08545 0.04102q-0.22217 0.11279-0.30078 0.33838-0.0752 0.22217 0.00684 0.43408 0.05811 0.0957 0.14013 0.18115 0.08545 0.08203 0.16748 0.11963 0.08545 0.03418 0.33838 0.03418l3.40088 0 0 3.40088q0 0.25293 0.03418 0.33838 0.0376 0.08203 0.11963 0.16748 0.08545 0.08203 0.18799 0.1333 0.10596 0.04785 0.23242 0.04785 0.12646 0 0.229-0.04785 0.10596-0.05127 0.18799-0.1333 0.08545-0.08545 0.11963-0.16748 0.0376-0.08545 0.0376-0.33838l0-3.40088 3.40088 0q0.25293 0 0.33496-0.03418 0.08545-0.0376 0.16748-0.11963 0.08545-0.08545 0.1333-0.18799 0.05127-0.10596 0.05127-0.23242 0-0.12646-0.04102-0.23926-0.09912-0.19482-0.29394-0.29394l-0.08545-0.04102-3.66748 0 0-3.38721q0-0.2666-0.02734-0.32128-0.07178-0.19824-0.25293-0.30079-0.18115-0.10596-0.39307-0.06494z"
              fill="currentColor"
            ></path>
          </svg>
          <span
            data-pencil-name="New Chat Label"
            class="text-xs sm:text-[13px] leading-normal font-semibold text-left whitespace-nowrap text-[#FFFFFF] dark:text-[#111111]"
          >
            New chat
          </span>
        </button>

        <!-- Avatar / User Profile -->
        <div class="relative">
          <button
            id="user-menu-button"
            type="button"
            data-pencil-name="Avatar"
            class="w-[30px] h-[30px] sm:w-[32px] sm:h-[32px] shrink-0 bg-[#D9D9E3] dark:bg-[#2E2E2E] rounded-full flex items-center justify-center text-xs font-semibold text-neutral-700 dark:text-[#ECECEC] hover:ring-2 hover:ring-neutral-300 dark:hover:ring-neutral-700 active:scale-95 transition cursor-pointer select-none"
          >
            @auth
              {{ substr(auth()->user()->name, 0, 1) }}
            @endauth
          </button>

          <!-- User Menu Dropdown -->
          <div
            id="user-menu-dropdown"
            class="hidden absolute right-0 mt-1.5 w-48 bg-white dark:bg-[#1A1A1A] rounded-xl shadow-2xl border border-[#E5E7EB] dark:border-[#262626] py-1 z-50 text-left"
          >
            @auth
              <div class="px-3 py-2 border-b border-[#E5E7EB] dark:border-[#262626]">
                <p class="text-[13px] font-semibold text-[#111111] dark:text-[#ECECEC] truncate">{{ auth()->user()->name }}</p>
                <p class="text-[11px] text-[#6B7280] dark:text-[#B4B4B4] truncate">{{ auth()->user()->email }}</p>
              </div>
              <a
                href="{{ route('dashboard') }}"
                class="block px-3 py-2 text-[13px] text-[#111111] dark:text-[#ECECEC] hover:bg-neutral-50 dark:hover:bg-[#262626] transition"
              >
                Dashboard
              </a>
              <a
                href="{{ route('profile.edit') }}"
                class="block px-3 py-2 text-[13px] text-[#111111] dark:text-[#ECECEC] hover:bg-neutral-50 dark:hover:bg-[#262626] transition"
              >
                Settings
              </a>
              <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button
                  type="submit"
                  class="w-full text-left px-3 py-2 text-[13px] text-red-600 dark:text-red-400 hover:bg-neutral-50 dark:hover:bg-[#262626] transition cursor-pointer"
                >
                  Log out
                </button>
              </form>
            @else
              <a
                href="{{ route('login') }}"
                class="block px-3 py-2 text-[13px] text-[#111111] dark:text-[#ECECEC] hover:bg-neutral-50 dark:hover:bg-[#262626] transition font-medium"
              >
                Log in
              </a>
              @if (Route::has('register'))
                <a
                  href="{{ route('register') }}"
                  class="block px-3 py-2 text-[13px] text-[#111111] dark:text-[#ECECEC] hover:bg-neutral-50 dark:hover:bg-[#262626] transition"
                >
                  Register
                </a>
              @endif
            @endauth
          </div>
        </div>
      </div>
    </header>

    <!-- Body Layout -->
    <div
      data-pencil-name="Body"
      class="w-full flex-1 min-h-0 flex flex-row overflow-hidden relative z-10"
    >
      <!-- Mobile Backdrop -->
      <div
        id="sidebar-backdrop"
        class="hidden fixed inset-0 bg-black/50 backdrop-blur-xs z-40 md:hidden transition-opacity duration-300"
      ></div>

      <!-- Sidebar -->
      <aside
        id="sidebar"
        data-pencil-name="Sidebar"
        class="fixed md:static inset-y-0 left-0 w-[290px] sm:w-[310px] max-w-[85vw] h-full flex flex-col justify-between p-3.5 sm:p-4 bg-[#F9F9FA] dark:bg-[#0E0E0E] md:bg-[#F9F9FA] md:dark:bg-[#000000] border-r border-[#E5E7EB] dark:border-[#262626] z-50 md:z-10 transition-transform duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] -translate-x-full md:translate-x-0 shadow-2xl md:shadow-none select-none"
      >
        <div class="w-full flex-1 min-h-0 flex flex-col gap-2.5 overflow-hidden">
          <!-- Sidebar Header -->
          <div
            data-pencil-name="Sidebar Header"
            class="shrink-0 w-full flex flex-row justify-between items-center py-1"
          >
            <div class="flex items-center gap-2">
              <span
                data-pencil-name="Sidebar Title"
                class="text-[13px] leading-normal text-[#111111] dark:text-[#ECECEC] font-semibold text-left whitespace-nowrap"
              >
                Menu & Chat
              </span>
            </div>
            <div class="flex items-center gap-1">
              <!-- Close button for mobile -->
              <button
                type="button"
                onclick="closeMobileSidebar()"
                class="md:hidden p-1.5 rounded-lg hover:bg-neutral-200/60 dark:hover:bg-[#1A1A1A] text-[#6B7280] dark:text-[#B4B4B4] active:scale-90 transition cursor-pointer"
                title="Tutup menu"
                aria-label="Close sidebar"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
              <!-- New Chat button -->
              <button
                type="button"
                onclick="startNewChat()"
                class="p-1.5 rounded-lg hover:bg-neutral-200/60 dark:hover:bg-[#1A1A1A] text-[#6B7280] dark:text-[#B4B4B4] hover:text-[#111111] dark:hover:text-[#ECECEC] active:scale-90 transition cursor-pointer"
                title="New Chat"
              >
                <svg
                  data-pencil-name="Edit Icon"
                  viewBox="0 0 14 14"
                  class="w-[16px] h-[16px] text-[#6B7280] dark:text-[#B4B4B4]"
                >
                  <path
                    d="M11.26904 0.61523q-0.18115 0.02734-0.3999 0.12647-0.21533 0.09912-0.38281 0.21191-0.12646 0.0957-2.84375 2.81983-2.71387 2.72412-2.78565 2.83349-0.12646 0.18457-0.19824 0.3794-0.06836 0.19482-0.3042 0.98096l-0.21191 0.76904q-0.05469 0.2085-0.06153 0.29394-0.00684 0.08203 0.02051 0.19483l0 0.01367q0.07178 0.25293 0.23926 0.42041 0.16748 0.16748 0.42041 0.23926l0.01367 0q0.11279 0.02734 0.19483 0.02051 0.08545-0.00684 0.29394-0.06153l0.76904-0.21191q0.78613-0.23584 0.98096-0.3042 0.19482-0.07178 0.3794-0.19824 0.10938-0.08545 2.83349-2.79932 2.72412-2.71729 2.81983-2.85742 0.19824-0.25293 0.29394-0.58789 0.09912-0.33496 0.05811-0.65967-0.05811-0.57422-0.40674-0.99121-0.34863-0.42041-0.90918-0.58789-0.15381-0.05811-0.43408-0.05811-0.28027 0-0.3794 0.01367z m-8.66455 0.58789q-0.50586 0.08545-0.87842 0.4375-0.36914 0.34863-0.50927 0.86475-0.02734 0.08545-0.02735 0.71436l-0.01367 3.78027 0.01367 3.78027q0 0.62891 0.02735 0.71436 0.14014 0.48877 0.46826 0.82031 0.33154 0.32813 0.82031 0.46826 0.08545 0.02734 0.71436 0.02735l3.78027 0.01367 3.78027-0.01367q0.62891 0 0.71436-0.02735 0.48877-0.14014 0.81689-0.46826 0.33154-0.33154 0.47168-0.82031 0.02734-0.08545 0.02735-0.42041l0.01367-4.2417-0.04102-0.08545q-0.09912-0.16748-0.28027-0.2666-0.05811-0.02734-0.09912-0.04102-0.04102-0.01367-0.15381-0.01367-0.11279 0-0.15381 0.01367-0.04101 0.01367-0.11279 0.04102-0.18115 0.09912-0.28027 0.2666l-0.04102 0.08545-0.01367 4.38183-0.04102 0.09913q-0.14014 0.27685-0.43408 0.33496-0.09912 0.02734-4.18701 0.02734-4.08789 0-4.18701-0.02734-0.29395-0.05811-0.43408-0.33496l-0.04102-0.09913 0-8.42871 0.04102-0.09912q0.09912-0.2085 0.30761-0.30761l0.09912-0.04102 4.38184-0.01367 0.08545-0.04102q0.06836-0.04443 0.15039-0.12646 0.08545-0.08545 0.12647-0.16065 0.04443-0.07861 0.04443-0.24609 0-0.16748-0.04102-0.25293-0.09912-0.16748-0.28027-0.2666l-0.08545-0.05469-2.2251 0q-2.2251 0-2.33789 0.02735z m9.11231 0.56055q0.14014 0.02734 0.2666 0.12647 0.12647 0.09912 0.18799 0.22558 0.06494 0.12305 0.06494 0.29395 0 0.16748-0.05469 0.29394-0.04443 0.06836-2.73437 2.77198-2.68652 2.7002-2.76856 2.74121-0.04102 0.03076-0.65967 0.21191-0.61523 0.18115-0.62207 0.17432-0.00684-0.00684 0.17432-0.62207 0.18115-0.61865 0.21533-0.67334 0.0376-0.05469 2.73779-2.74805 2.70361-2.69678 2.7583-2.72412 0.05811-0.03076 0.14698-0.05811 0.09228-0.02734 0.1333-0.02734 0.04443 0 0.15381 0.01367z"
                    fill="currentColor"
                  ></path>
                </svg>
              </button>
            </div>
          </div>

          <!-- KHUSUS MOBILE: Controls Section (Model Selection Modal Trigger, Search Mode, Agent Mode) -->
          <div
            data-pencil-name="Mobile Controls"
            class="md:hidden shrink-0 flex flex-col gap-2 pt-1 pb-3 border-b border-[#E5E7EB]/70 dark:border-[#262626]"
          >
            <div class="flex items-center justify-between px-0.5">
              <span class="text-[10px] font-bold uppercase tracking-wider text-[#9CA3AF] dark:text-[#737373]">
                Pengaturan AI
              </span>
            </div>

            <!-- Mobile Model Selector Button (Triggers Modal) -->
            <button
              id="mobile-model-trigger-btn"
              type="button"
              onclick="openMobileModelModal()"
              class="w-full flex flex-row items-center justify-between p-2.5 rounded-xl bg-white dark:bg-[#161618] border border-[#E5E7EB] dark:border-[#282828] hover:border-neutral-300 dark:hover:border-neutral-700 active:scale-[0.98] transition-all duration-200 cursor-pointer shadow-2xs text-left"
            >
              <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-neutral-100 dark:bg-[#222224] flex items-center justify-center shrink-0 text-[#111111] dark:text-[#ECECEC]">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                  </svg>
                </div>
                <div class="flex flex-col min-w-0">
                  <span class="text-[10px] text-[#6B7280] dark:text-[#9CA3AF] uppercase font-medium tracking-wide">Model AI</span>
                  <span id="mobile-model-display-name" class="text-xs font-semibold text-[#111111] dark:text-[#ECECEC] truncate">
                    GPT-4o
                  </span>
                </div>
              </div>
              <div class="flex items-center gap-1.5 shrink-0">
                <span id="mobile-model-provider-badge" class="text-[10px] px-1.5 py-0.5 rounded bg-neutral-100 dark:bg-[#252525] text-[#4B5563] dark:text-[#D1D5DB] font-medium">
                  Moonshot
                </span>
                <svg class="w-4 h-4 text-[#9CA3AF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
              </div>
            </button>

            <!-- Mode Switches Group (Search Switch Mode & Agent Mode) -->
            <div class="flex flex-col gap-1.5 w-full">
              <!-- Search Mode Switch -->
              <button
                id="mobile-search-toggle-btn"
                type="button"
                onclick="toggleSearchMode()"
                class="w-full flex flex-row items-center justify-between p-2 rounded-xl bg-white dark:bg-[#161618] border border-[#E5E7EB] dark:border-[#282828] active:scale-[0.98] transition-all duration-200 cursor-pointer shadow-2xs select-none"
              >
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 rounded-lg bg-neutral-100 dark:bg-[#222224] flex items-center justify-center shrink-0 text-[#6B7280] dark:text-[#B4B4B4]">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 14 14" fill="currentColor">
                      <path d="M6.00537 1.17578q-0.04102 0.01367-0.23584 0.02734-0.57422 0.07178-1.16211 0.29053-0.58789 0.21533-1.09375 0.5503-0.39307 0.2666-0.74853 0.61181-0.35547 0.3418-0.62207 0.7041-0.646 0.92285-0.86817 2.03028-0.05811 0.28027-0.07861 0.46142-0.02051 0.18115-0.02051 0.57422 0 0.43408 0.02734 0.68018 0.02734 0.24268 0.12647 0.59472 0.19482 0.75537 0.60156 1.43555 0.40674 0.67676 0.96729 1.18262 0.40674 0.3623 0.81689 0.60156 0.41357 0.23926 0.91944 0.43408 1.06299 0.39307 2.21826 0.29395 1.15527-0.09912 2.1499-0.65967 0.12646-0.06836 0.33496-0.2085 0.2085-0.14014 0.29395-0.21191l0.05468-0.05469 1.10743 1.10742q1.12109 1.104 1.20312 1.15528 0.08545 0.04785 0.23926 0.04785 0.15381 0 0.23584-0.03418 0.08545-0.0376 0.18115-0.1333 0.09912-0.09912 0.1333-0.18115 0.0376-0.08545 0.0376-0.23926 0-0.15381-0.05127-0.23584-0.04785-0.08545-1.15186-1.20654l-1.10742-1.10743 0.05469-0.06836q0.05811-0.05469 0.19824-0.25293 0.82373-1.23047 0.89209-2.70703 0.07178-1.47656-0.63916-2.76513-0.5332-0.96729-1.42187-1.64405-0.88867-0.68018-1.95508-0.9331-0.29395-0.06836-0.54004-0.09571-0.24268-0.03076-0.60498-0.04443-0.43408 0-0.49219 0z m0.7417 1.17578q0.37939 0.01367 0.79297 0.14014 0.41357 0.12646 0.78955 0.32129 0.7417 0.39307 1.28857 1.08008 0.54687 0.68359 0.75538 1.49707 0.07178 0.28027 0.09228 0.49902 0.02051 0.21533 0.02051 0.50928 0 0.70068-0.19483 1.30224-0.39307 1.16211-1.33984 1.91065-0.94336 0.74853-2.16016 0.875-0.19824 0.01367-0.49218 0.00683-0.29395-0.00684-0.48877-0.03418-0.5332-0.08545-1.03907-0.30761-0.50244-0.22559-0.93652-0.56055-0.14014-0.12646-0.35205-0.3418-0.2085-0.21875-0.33154-0.37256-0.57422-0.76904-0.7417-1.69531-0.08545-0.43408-0.06494-0.90918 0.02051-0.4751 0.11963-0.89551 0.29395-1.14844 1.18945-1.95849 0.89551-0.81348 2.05762-1.02539 0.18115-0.02734 0.42041-0.04102 0.23926-0.01367 0.34863-0.01367l0.2666 0.01367z"/>
                    </svg>
                  </div>
                  <div class="flex flex-col text-left">
                    <span class="text-xs font-medium text-[#111111] dark:text-[#ECECEC]">Search Mode</span>
                    <span id="mobile-search-status-label" class="text-[10px] text-[#6B7280] dark:text-[#9CA3AF]">Aktif</span>
                  </div>
                </div>
                <div id="mobile-search-track" class="w-[34px] h-[19px] shrink-0 flex flex-row p-[2px] justify-end items-center rounded-[10px] bg-[#111111] dark:bg-[#FFFFFF] transition-colors duration-200">
                  <div id="mobile-search-knob" class="w-[15px] h-[15px] bg-[#FFFFFF] dark:bg-[#0A0A0A] rounded-full transition-transform duration-200"></div>
                </div>
              </button>

              <!-- Agent Mode Switch -->
              <button
                id="mobile-agent-toggle-btn"
                type="button"
                onclick="toggleAgentMode()"
                class="w-full flex flex-row items-center justify-between p-2 rounded-xl bg-white dark:bg-[#161618] border border-[#E5E7EB] dark:border-[#282828] active:scale-[0.98] transition-all duration-200 cursor-pointer shadow-2xs select-none"
              >
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 rounded-lg bg-neutral-100 dark:bg-[#222224] flex items-center justify-center shrink-0 text-[#6B7280] dark:text-[#B4B4B4]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-12h9v9h-9v-9Z" />
                    </svg>
                  </div>
                  <div class="flex flex-col text-left">
                    <span class="text-xs font-medium text-[#111111] dark:text-[#ECECEC]">Agent Mode</span>
                    <span id="mobile-agent-status-label" class="text-[10px] text-[#6B7280] dark:text-[#9CA3AF]">Nonaktif</span>
                  </div>
                </div>
                <div id="mobile-agent-track" class="w-[34px] h-[19px] shrink-0 flex flex-row p-[2px] justify-start items-center rounded-[10px] bg-neutral-300 dark:bg-[#333333] transition-colors duration-200">
                  <div id="mobile-agent-knob" class="w-[15px] h-[15px] bg-[#FFFFFF] dark:bg-[#B4B4B4] rounded-full transition-transform duration-200"></div>
                </div>
              </button>
            </div>
          </div>

          <!-- Recent Chats Section (Takes all remaining vertical space via flex-1 min-h-0) -->
          <div class="flex-1 min-h-0 flex flex-col overflow-hidden pt-1">
            <div class="shrink-0 flex items-center justify-between px-1 mb-1.5">
              <span
                data-pencil-name="Recent Label"
                class="text-[12px] leading-normal text-[#6B7280] dark:text-[#B4B4B4] font-semibold text-left whitespace-nowrap"
              >
                Recent chats
              </span>
            </div>

            <!-- Session List -->
            <div
              id="session-list"
              data-pencil-name="Session List"
              class="w-full flex-1 min-h-0 flex flex-col gap-[4px] overflow-y-auto pr-1 overscroll-contain chat-scroll"
            >
              <!-- Rendered dynamically via JavaScript -->
            </div>
          </div>
        </div>

        <!-- Sidebar Footer -->
        <div
          data-pencil-name="Sidebar Footer"
          class="w-full flex flex-col gap-[4px] pt-3 border-t border-[#E5E7EB]/60 dark:border-[#262626]"
        >
          <!-- Sidebar Theme Toggle Row -->
          <button
            type="button"
            onclick="toggleTheme()"
            class="w-full flex flex-row gap-[10px] p-[10px_12px] items-center rounded-xl hover:bg-neutral-200/50 dark:hover:bg-[#1A1A1A] active:scale-95 transition-all duration-200 cursor-pointer text-left select-none text-[#111111] dark:text-[#ECECEC]"
          >
            <svg class="w-[16px] h-[16px] shrink-0 text-[#6B7280] dark:text-[#B4B4B4] block dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
            <svg class="w-[16px] h-[16px] shrink-0 text-[#6B7280] dark:text-[#B4B4B4] hidden dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="5"></circle>
              <line x1="12" y1="1" x2="12" y2="3"></line>
              <line x1="12" y1="21" x2="12" y2="23"></line>
              <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
              <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
              <line x1="1" y1="12" x2="3" y2="12"></line>
              <line x1="21" y1="12" x2="23" y2="12"></line>
              <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
              <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
            </svg>
            <span class="text-[13px] leading-normal font-normal text-[#111111] dark:text-[#ECECEC]">
              <span class="dark:hidden">Switch to Dark mode</span>
              <span class="hidden dark:inline">Switch to Light mode</span>
            </span>
          </button>

          <!-- Settings Row -->
          <a
            href="{{ Route::has('profile.edit') ? route('profile.edit') : '#' }}"
            data-pencil-name="Settings Row"
            class="w-full flex flex-row gap-[10px] p-[10px_12px] items-center rounded-xl hover:bg-neutral-200/50 dark:hover:bg-[#1A1A1A] active:scale-95 transition-all duration-200 cursor-pointer text-decoration-none"
          >
            <svg
              data-pencil-name="Settings Icon"
              viewBox="0 0 14 14"
              class="w-[16px] h-[16px] shrink-0 text-[#6B7280] dark:text-[#B4B4B4]"
            >
              <path
                d="M6.80518 0.60156q-0.03076 0.01367-0.15381 0.02735-0.32471 0.05811-0.63916 0.23925-0.31445 0.18115-0.50928 0.43409-0.19824 0.23926-0.29053 0.46484-0.08887 0.22217-0.15722 0.5708-0.03076 0.18115-0.07178 0.28711-0.04102 0.10596-0.1333 0.19824-0.08887 0.08887-0.2085 0.15381-0.11963 0.06152-0.24609 0.08887-0.19482 0.02734-0.48877-0.08545-0.19824-0.08203-0.33838-0.10938-0.13672-0.03076-0.36231-0.03076-0.28027-0.01367-0.44775 0.03076-0.54688 0.10938-0.93994 0.48194-0.38965 0.37256-0.54346 0.88525-0.15381 0.50928-0.01367 1.03906 0.16748 0.61523 0.72803 1.06299 0.18115 0.15723 0.26318 0.3042 0.08545 0.14697 0.08545 0.35547 0 0.2085-0.08545 0.35547-0.08203 0.14697-0.26318 0.3042-0.40674 0.32129-0.58789 0.69726-0.21191 0.42041-0.21192 0.875 0 0.45459 0.21192 0.875 0.19482 0.37939 0.52978 0.646 0.33838 0.2666 0.7417 0.3623 0.60156 0.15381 1.26123-0.11279 0.25293-0.0957 0.4751-0.06152 0.22559 0.03418 0.41357 0.21191 0.18799 0.17432 0.23243 0.49561 0.05469 0.37598 0.20849 0.68701 0.22559 0.46143 0.67334 0.74853 0.44775 0.28711 0.96387 0.31446 0.61865 0.02734 1.1416-0.30078 0.52637-0.32813 0.76221-0.90235 0.08545-0.22558 0.12646-0.51269 0.04443-0.28711 0.16748-0.44092 0.12646-0.15381 0.29395-0.23926 0.31104-0.14014 0.68701 0 0.22559 0.09912 0.39307 0.13672 0.16748 0.03418 0.37597 0.03418 0.32471 0 0.61865-0.09912 0.43408-0.14014 0.75538-0.46143 0.46143-0.44775 0.55712-1.06299 0.09912-0.61865-0.18115-1.19287-0.18115-0.37598-0.57422-0.68359-0.19482-0.15381-0.28027-0.30762-0.08203-0.15723-0.08203-0.36572 0-0.2085 0.08203-0.35547 0.08545-0.14697 0.2666-0.3042 0.30762-0.23584 0.47852-0.51611 0.29395-0.43408 0.32129-0.94336 0.02734-0.5127-0.20508-0.97412-0.229-0.46143-0.65625-0.74854-0.42725-0.28711-0.91602-0.31787-0.25293-0.02734-0.47851 0.01026-0.22217 0.03418-0.48877 0.14697-0.2085 0.08203-0.35547 0.08203-0.14697 0-0.31787-0.08203-0.16748-0.08545-0.29395-0.23926-0.12305-0.15381-0.16748-0.44092-0.04102-0.28711-0.12646-0.51269-0.18115-0.43408-0.55371-0.74854-0.36914-0.31445-0.84424-0.41357-0.08545-0.02734-0.31787-0.04102-0.229-0.01367-0.28369 0z"
                fill="currentColor"
              ></path>
            </svg>
            <span
              data-pencil-name="Settings Label"
              class="text-[13px] leading-normal text-[#111111] dark:text-[#ECECEC] font-normal text-left whitespace-nowrap"
            >
              Settings
            </span>
          </a>
        </div>
      </aside>

      <!-- Main Chat Area -->
      <main
        data-pencil-name="Main"
        class="flex-1 h-full min-h-0 flex flex-col justify-between bg-[#FFFFFF] dark:bg-[#0A0A0A] overflow-hidden relative transition-colors duration-200"
      >
        <!-- Chat Scroll -->
        <div
          id="chat-scroll"
          data-pencil-name="Chat Scroll"
          class="chat-scroll relative flex-1 min-h-0 w-full overflow-y-auto overflow-x-hidden px-3 sm:px-6 py-4 sm:py-6 flex flex-col items-center gap-3 sm:gap-4 bg-[#FFFFFF] dark:bg-[#0A0A0A] overscroll-contain"
        >
          <!-- Chat Column -->
          <div
            id="chat-column"
            data-pencil-name="Chat Column"
            class="w-full max-w-[768px] flex flex-col gap-3 sm:gap-4 justify-start items-start relative pb-2"
          >
            <!-- Chat messages dynamically rendered here -->
          </div>
        </div>

        <!-- Floating Scroll to Bottom Button -->
        <button
          id="scroll-bottom-btn"
          type="button"
          class="hidden fixed bottom-24 sm:bottom-28 left-1/2 -translate-x-1/2 z-30 flex items-center gap-1.5 px-3.5 py-1.5 bg-[#111111]/90 dark:bg-[#ECECEC]/90 text-white dark:text-[#111111] text-xs font-medium rounded-full shadow-lg backdrop-blur-md hover:opacity-90 active:scale-95 transition-all cursor-pointer select-none animate-fade-in"
          onclick="scrollToBottomSmooth()"
          aria-label="Scroll to bottom"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3"/>
          </svg>
          <span>Pesan terbaru</span>
        </button>

        <!-- Input Bar -->
        <div
          data-pencil-name="Input Bar"
          class="w-full shrink-0 flex flex-col gap-1.5 sm:gap-2 px-3 sm:px-6 pt-2 pb-[max(10px,env(safe-area-inset-bottom))] items-center bg-[#FFFFFF]/95 dark:bg-[#0A0A0A]/95 backdrop-blur-md border-t border-[#E5E7EB] dark:border-[#262626] relative z-20 transition-colors duration-200"
        >
          <div
            data-pencil-name="Input Box Wrap"
            class="w-full max-w-[768px] flex flex-col gap-1.5 sm:gap-2 items-start"
          >
            <!-- Input Box -->
            <form
              id="chat-form"
              onsubmit="handleSendMessage(event)"
              data-pencil-name="Input Box"
              class="w-full flex flex-row gap-1.5 sm:gap-2 p-1.5 sm:p-2 items-end bg-[#FFFFFF] dark:bg-[#161618] border border-[#E5E7EB] dark:border-[#2C2C2C] rounded-2xl focus-within:border-neutral-400 dark:focus-within:border-neutral-600 focus-within:ring-2 focus-within:ring-neutral-400/20 shadow-xs transition-all duration-200"
            >
              <!-- Attach Button -->
              <button
                type="button"
                class="w-8 h-8 sm:w-9 sm:h-9 shrink-0 flex items-center justify-center rounded-xl text-[#6B7280] dark:text-[#B4B4B4] hover:text-[#111111] dark:hover:text-[#ECECEC] hover:bg-neutral-100 dark:hover:bg-[#222222] active:scale-90 transition-all cursor-pointer p-0.5 mb-0.5"
                title="Attach file"
              >
                <svg
                  data-pencil-name="Attach Icon"
                  viewBox="0 0 14 14"
                  class="w-[18px] h-[18px] shrink-0 text-[#6B7280] dark:text-[#B4B4B4]"
                >
                  <path
                    d="M9.21143 0.58789q-0.33496 0.01367-0.69385 0.11963-0.35547 0.10596-0.64942 0.27344-0.2085 0.12646-0.35888 0.25293-0.14697 0.12305-0.38282 0.3623-0.78613 0.78271-2.87109 2.92578-2.08496 2.14307-2.17041 2.2251-0.38965 0.47852-0.62891 1.09375-0.23926 0.61523-0.28027 1.24414-0.05469 0.88184 0.25293 1.69531 0.30762 0.81006 0.93652 1.44239 0.95361 0.9502 2.33789 1.16211 0.21191 0.02734 0.54688 0.02734 0.33496 0 0.54687-0.02734 1.14844-0.18115 1.99952-0.86817 0.1709-0.12647 0.58789-0.54687 0.42041-0.42041 2.18408-2.2251l2.34131-2.40967q0.24951-0.24951 0.26318-0.30762l0-0.01367q0.0581-0.16748 0.02735-0.32812-0.02734-0.16064-0.15381-0.28711-0.16748-0.16748-0.41699-0.16748l-0.04444 0q-0.08203-0.01367-0.14014 0.01367-0.0957 0.05469-0.28711 0.23242-0.18799 0.17432-0.62206 0.62207l-1.72266 1.75q-2.46436 2.51904-2.61817 2.64551-0.58789 0.54688-1.45605 0.71435-0.16748 0.04102-0.50928 0.03418-0.3418-0.00684-0.52636-0.04785-0.65625-0.14014-1.16211-0.50586-0.2085-0.14014-0.44092-0.38965-0.229-0.25293-0.35547-0.47851-0.46143-0.78271-0.39307-1.7124 0.07178-0.93311 0.65967-1.63379 0.10938-0.14014 2.15332-2.2251l2.50879-2.54639q0.51611-0.5332 0.61523-0.60498 0.2666-0.19482 0.61524-0.29394 0.16748-0.04102 0.41357-0.04785 0.24609-0.00684 0.41358 0.04785 0.43408 0.09912 0.7622 0.39306 0.32813 0.29395 0.48194 0.68702 0.05811 0.18115 0.07861 0.2871 0.02051 0.10254 0.02051 0.32813 0 0.22559-0.02051 0.33154-0.02051 0.10254-0.07861 0.28369-0.08203 0.23926-0.23584 0.43409-0.05811 0.07178-2.59082 2.65918l-1.77735 1.8081q-0.44775 0.46143-0.64599 0.64258-0.19482 0.18115-0.28028 0.22559-0.05469 0.02734-0.14013 0.02734l-0.05469 0q-0.08545 0-0.1333-0.00684-0.04785-0.00684-0.11963-0.04785-0.18115-0.09912-0.28027-0.28027-0.04102-0.08545-0.04102-0.23926 0-0.09912 0.01367-0.14697 0.01367-0.04785 0.04102-0.10938 0.03076-0.06494 0.47168-0.51953 0.44092-0.45459 1.8833-1.92431l2.51904-2.57715q0.19482-0.21191 0.24951-0.30762 0.05811-0.09912 0.05811-0.25293 0-0.23926-0.16748-0.40674-0.09912-0.0957-0.18457-0.12988-0.08203-0.0376-0.22217-0.0376l-0.05469 0q-0.08545 0-0.14013 0.02734-0.08545 0.04443-0.27344 0.21875-0.18799 0.17432-0.62207 0.6084l-1.75 1.77735q-2.49512 2.5498-2.57715 2.6455-0.25293 0.28027-0.36572 0.71436-0.04102 0.14014-0.04102 0.40674 0 0.18115 0.00684 0.25976 0.00684 0.0752 0.03418 0.18799 0.11279 0.42041 0.38623 0.72803 0.27344 0.30762 0.68017 0.46143 0.28027 0.11279 0.61524 0.11279 0.33496 0 0.60156-0.0957 0.2666-0.09912 0.4751-0.25293 0.07178-0.05811 2.51904-2.54981 2.45068-2.4917 2.6626-2.73096 0.29395-0.32129 0.48193-0.72461 0.18799-0.40674 0.2461-0.85449 0.08203-0.646-0.13672-1.28857-0.21533-0.646-0.67676-1.1211-0.43408-0.43408-1.00146-0.66308-0.56738-0.23242-1.19629-0.20508z"
                    fill="currentColor"
                  ></path>
                </svg>
              </button>

              <!-- Textarea Input (Supports Shift+Enter for multiline) -->
              <textarea
                id="message-input"
                rows="1"
                placeholder="Message AI..."
                data-pencil-name="Placeholder"
                class="flex-1 min-w-0 bg-transparent border-0 outline-none px-2 py-1.5 text-[15px] sm:text-[14px] text-[#111111] dark:text-[#ECECEC] placeholder:text-[#6B7280] dark:placeholder:text-[#B4B4B4] focus:ring-0 focus:outline-none resize-none max-h-36 overflow-y-auto leading-relaxed chat-scroll"
              ></textarea>

              <!-- Mic Button -->
              <button
                type="button"
                id="mic-button"
                class="w-8 h-8 sm:w-9 sm:h-9 shrink-0 flex items-center justify-center rounded-xl text-[#6B7280] dark:text-[#B4B4B4] hover:text-[#111111] dark:hover:text-[#ECECEC] hover:bg-neutral-100 dark:hover:bg-[#222222] active:scale-90 transition-all cursor-pointer p-0.5 mb-0.5"
                title="Voice input"
              >
                <svg
                  data-pencil-name="Mic Icon"
                  viewBox="0 0 14 14"
                  class="w-[18px] h-[18px] shrink-0 text-[#6B7280] dark:text-[#B4B4B4]"
                >
                  <path
                    d="M6.90088 0.58789q-0.64258 0.02734-1.18262 0.38623-0.53662 0.35547-0.80322 0.92969-0.14014 0.28027-0.19824 0.54687-0.02734 0.0957-0.02735 0.4751l-0.01367 4.40918 0.04102 0.16748q0.19824 0.78613 0.75879 1.26123 0.43408 0.36572 1.02197 0.51953 0.10938 0.02734 0.1914 0.03418 0.08545 0.00684 0.31104 0.00684 0.28027 0 0.43408-0.02734 0.15381-0.02734 0.3794-0.1128 0.46143-0.16748 0.82373-0.52295 0.36572-0.35889 0.54687-0.83398 0.04102-0.12646 0.08545-0.29395l0.05469-0.1709 0-4.15625q0-0.48877-0.02734-0.68701-0.01367-0.12646-0.07178-0.29394l-0.01367-0.02735q-0.23584-0.70068-0.80323-1.13476-0.56738-0.43408-1.30908-0.4751l-0.19824 0z m0.33838 1.18945q0.29395 0.05811 0.52978 0.26661 0.23926 0.2085 0.32129 0.47509 0.04443 0.12646 0.06494 0.3042 0.02051 0.17432 0.00684 2.25928l0 1.77734q-0.01367 0.36572-0.02734 0.43409-0.08545 0.29395-0.31788 0.52636-0.229 0.229-0.52294 0.31446-0.11279 0.02734-0.29395 0.02734-0.18115 0-0.29395-0.02734-0.30762-0.08545-0.5332-0.31446-0.22217-0.23242-0.30762-0.52636-0.01367-0.06836-0.02734-0.43409l0-1.77734q-0.01367-2.08496 0-2.26953 0.04102-0.30762 0.19482-0.54346 0.19824-0.29395 0.52637-0.42724 0.32813-0.1333 0.68018-0.06495z m-4.46729 3.48633q-0.14014 0.04102-0.2666 0.16748-0.0957 0.08545-0.1333 0.17774-0.03418 0.08887-0.03418 0.31445-0.01367 0.18115 0 0.69727 0 0.61865 0.01367 0.83056 0.01367 0.2085 0.08545 0.51612 0.0957 0.4751 0.31104 0.96044 0.21875 0.48193 0.52636 0.88526 0.46484 0.60156 1.09375 1.03564 0.62891 0.43408 1.35694 0.646 0.40674 0.11279 0.63232 0.12646l0.05469 0.01368 0.01367 0.70068q0 0.41699 0.01367 0.52978 0 0.16748 0.02735 0.22559 0.03076 0.05469 0.11279 0.14014l0.01367 0.01367q0.16748 0.16748 0.40674 0.16748 0.23926 0 0.40674-0.16748l0.01367-0.01367q0.08203-0.08545 0.10938-0.14014 0.03076-0.05811 0.03076-0.22559 0.01367-0.11279 0.01367-0.52978l0.01367-0.70068 0.05469-0.01368q0.04443 0 0.22558-0.04443 1.06299-0.19482 1.92432-0.8374 0.86133-0.646 1.3501-1.59619 0.36572-0.71436 0.49219-1.54151 0.02734-0.19482 0.02734-0.96386 0.01367-0.5332 0-0.70069 0-0.23926-0.0376-0.32812-0.03418-0.09229-0.12988-0.17774-0.18115-0.19482-0.44775-0.17431-0.2666 0.02051-0.42042 0.23242-0.09912 0.10938-0.11279 0.24267-0.01367 0.1333-0.01367 0.70752 0 0.54688-0.01367 0.78614-0.01367 0.23584-0.05469 0.47509-0.08545 0.38965-0.27685 0.79639-0.18799 0.40674-0.46827 0.72803-0.48877 0.60156-1.17578 0.92627-0.71436 0.34863-1.51074 0.34863-0.79639 0-1.52441-0.34863-0.49219-0.23926-0.88526-0.61866-0.9502-0.90918-1.07666-2.21142-0.01367-0.19482-0.01367-0.88184 0-0.57422-0.01367-0.69726-0.01367-0.12646-0.08545-0.23926-0.0957-0.14014-0.28027-0.2085-0.18115-0.07178-0.34864-0.03076z"
                    fill="currentColor"
                  ></path>
                </svg>
              </button>

              <!-- Submit Button -->
              <button
                type="submit"
                id="send-button"
                data-pencil-name="Submit Button"
                class="w-8 h-8 sm:w-9 sm:h-9 shrink-0 flex items-center justify-center bg-[#111111] dark:bg-[#FFFFFF] hover:bg-neutral-800 dark:hover:bg-neutral-200 text-[#FFFFFF] dark:text-[#111111] rounded-xl active:scale-90 shadow-xs transition-all duration-150 cursor-pointer select-none mb-0.5"
                title="Send message"
              >
                <svg
                  data-pencil-name="Send Icon"
                  viewBox="0 0 14 14"
                  class="w-[18px] h-[18px] shrink-0 text-[#FFFFFF] dark:text-[#111111]"
                >
                  <path
                    d="M6.84619 2.35156q-0.08545 0.01367-0.15723 0.06494-0.06836 0.04785-2.16699 2.14649l-1.66455 1.69531q-0.36572 0.3623-0.46484 0.50244-0.05469 0.08545-0.05469 0.19824l0 0.04102q0 0.23926 0.18115 0.42041 0.11279 0.11279 0.28028 0.14697 0.16748 0.03418 0.32129-0.02051 0.08545-0.02734 0.32128-0.25976 0.23926-0.23242 1.37403-1.36377 1.58252-1.58252 1.59619-1.58252 0.01367 0 0.01367 3.42822l0 3.01123q0 0.44775 0.03418 0.53321 0.0376 0.08203 0.11963 0.16748 0.08545 0.08203 0.18799 0.1333 0.10596 0.04785 0.23242 0.04785 0.12646 0 0.229-0.04785 0.10596-0.05127 0.18799-0.1333 0.08545-0.08545 0.11963-0.16748 0.0376-0.08545 0.0376-0.53321l0-3.01123q0-3.42822 0.01367-3.42822 0.01367 0 1.59619 1.58252 1.13477 1.13135 1.37061 1.36377 0.23926 0.23242 0.32471 0.25976 0.15381 0.05469 0.32128 0.02051 0.16748-0.03418 0.28028-0.14697 0.18115-0.18115 0.18115-0.42041l0-0.04102q0-0.11279-0.05469-0.19824-0.09912-0.14014-0.46484-0.51611l-1.67822-1.68164q-2.10205-2.1123-2.17041-2.14649-0.06836-0.0376-0.16065-0.06494-0.09229-0.02734-0.14014-0.02734-0.04785 0-0.14697 0.02734z"
                    fill="currentColor"
                  ></path>
                </svg>
              </button>
            </form>

            <!-- Hint -->
            <div
              data-pencil-name="Hint"
              class="w-full text-[11px] sm:text-[12px] leading-tight text-[#6B7280] dark:text-[#B4B4B4] font-normal text-center select-none"
            >
              AI can make mistakes. Check important info. • Enter to send, Shift+Enter for newline
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- Mobile Model Selection Modal (Triggers from Mobile Sidebar) -->
    <div
      id="mobile-model-modal"
      class="hidden fixed inset-0 z-60 md:hidden flex items-end sm:items-center justify-center p-0 sm:p-4 transition-all duration-300"
      role="dialog"
      aria-modal="true"
      aria-labelledby="mobile-model-modal-title"
    >
      <!-- Backdrop -->
      <div
        id="mobile-model-modal-backdrop"
        class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity duration-300"
        onclick="closeMobileModelModal()"
      ></div>

      <!-- Modal Content (Bottom Sheet on Mobile, Dialog on Tablet) -->
      <div
        class="relative z-10 w-full sm:max-w-md max-h-[85vh] h-[80vh] sm:h-auto bg-white dark:bg-[#141416] rounded-t-3xl sm:rounded-2xl shadow-2xl border-t sm:border border-[#E5E7EB] dark:border-[#2A2A2E] flex flex-col overflow-hidden animate-fade-in"
      >
        <!-- Drag Handle Indicator -->
        <div class="sm:hidden w-full flex justify-center pt-3 pb-1 shrink-0">
          <div class="w-10 h-1 bg-neutral-300 dark:bg-neutral-700 rounded-full"></div>
        </div>

        <!-- Modal Header -->
        <div class="shrink-0 flex items-center justify-between px-4 py-3 border-b border-[#E5E7EB] dark:border-[#262626]">
          <div>
            <h3 id="mobile-model-modal-title" class="text-base font-semibold text-[#111111] dark:text-[#ECECEC]">
              Pilih Model AI
            </h3>
            <p class="text-xs text-[#6B7280] dark:text-[#9CA3AF]">
              Provider: <strong class="text-[#111111] dark:text-[#ECECEC]">Tokenportal.id</strong>
            </p>
          </div>
          <button
            type="button"
            onclick="closeMobileModelModal()"
            class="w-8 h-8 rounded-full flex items-center justify-center bg-neutral-100 dark:bg-[#222222] text-[#6B7280] dark:text-[#B4B4B4] hover:text-[#111111] dark:hover:text-[#ECECEC] active:scale-95 transition cursor-pointer"
            aria-label="Tutup modal"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Modal Search Input -->
        <div class="shrink-0 p-3 border-b border-[#E5E7EB] dark:border-[#262626] bg-[#FAFAFA] dark:bg-[#18181A]">
          <div class="relative">
            <input
              type="text"
              id="mobile-model-search-input"
              placeholder="Cari model..."
              class="w-full text-sm px-3 py-2 pl-8 rounded-xl bg-white dark:bg-[#222226] text-[#111111] dark:text-[#ECECEC] outline-none border border-[#E5E7EB] dark:border-[#333338] focus:border-neutral-400 dark:focus:border-neutral-500"
            />
            <svg class="w-4 h-4 text-[#9CA3AF] absolute left-2.5 top-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
          </div>
          <div class="flex items-center justify-between mt-2 px-1 text-xs text-[#6B7280] dark:text-[#9CA3AF]">
            <span>Model yang tersedia</span>
            <span id="mobile-model-count-label">Memuat...</span>
          </div>
        </div>

        <!-- Modal Model List -->
        <div
          id="mobile-model-list"
          class="flex-1 min-h-0 overflow-y-auto divide-y divide-[#F3F4F6] dark:divide-[#262626] p-2 space-y-1 overscroll-contain chat-scroll"
        >
          <!-- Dynamic model list items -->
        </div>

        <!-- Modal Footer -->
        <div class="shrink-0 p-3 border-t border-[#E5E7EB] dark:border-[#262626] bg-[#FAFAFA] dark:bg-[#18181A] flex items-center justify-between text-xs text-[#6B7280] dark:text-[#9CA3AF]">
          <div class="flex items-center gap-1.5 truncate pr-2">
            <span>Aktif:</span>
            <strong id="mobile-modal-active-model-name" class="text-[#111111] dark:text-[#ECECEC] font-medium truncate">-</strong>
          </div>
          <button
            type="button"
            onclick="closeMobileModelModal()"
            class="px-3.5 py-1.5 rounded-lg bg-neutral-200 dark:bg-[#2C2C30] text-[#111111] dark:text-[#ECECEC] font-medium active:scale-95 transition cursor-pointer"
          >
            Selesai
          </button>
        </div>
      </div>
    </div>

    <!-- Frontend Interactive Script -->
    <script>
      // Sessions & Models State
      const sessions = [];
      let availableModels = [];

      let activeSessionId = null;
      let isSearchActive = true;
      let isAgentActive = false;
      let activeModel = 'kimi-k27-code';

      // Elements
      const sessionListEl = document.getElementById('session-list');
      const chatColumnEl = document.getElementById('chat-column');
      const chatScrollEl = document.getElementById('chat-scroll');
      const messageInput = document.getElementById('message-input');
      const searchToggleBtn = document.getElementById('search-toggle-btn');
      const toggleTrack = document.getElementById('toggle-track');
      const toggleKnob = document.getElementById('toggle-knob');
      const agentToggleBtn = document.getElementById('agent-toggle-btn');
      const modelDropdownBtn = document.getElementById('model-dropdown-button');
      const modelDropdownMenu = document.getElementById('model-dropdown-menu');
      const modelDropdownArrow = document.getElementById('model-dropdown-arrow');
      const modelSearchInput = document.getElementById('model-search-input');
      const modelDropdownList = document.getElementById('model-dropdown-list');
      const modelCountLabel = document.getElementById('model-count-label');
      const modelProviderBadge = document.getElementById('model-provider-badge');
      const userMenuBtn = document.getElementById('user-menu-button');
      const userMenuDropdown = document.getElementById('user-menu-dropdown');
      const mobileSidebarToggle = document.getElementById('mobile-sidebar-toggle');
      const sidebarEl = document.getElementById('sidebar');
      const sidebarBackdrop = document.getElementById('sidebar-backdrop');
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

      // Mobile Model Elements
      const mobileModelModal = document.getElementById('mobile-model-modal');
      const mobileModelSearchInput = document.getElementById('mobile-model-search-input');
      const mobileModelList = document.getElementById('mobile-model-list');
      const mobileModelCountLabel = document.getElementById('mobile-model-count-label');
      const mobileModelDisplayName = document.getElementById('mobile-model-display-name');
      const mobileModelProviderBadge = document.getElementById('mobile-model-provider-badge');
      const mobileModalActiveModelName = document.getElementById('mobile-modal-active-model-name');

      // Theme Management
      function toggleTheme() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.theme = isDark ? 'dark' : 'light';
        updateSearchToggle();
        updateAgentToggle();
        renderSessions();
      }

      function toggleSearchMode() {
        isSearchActive = !isSearchActive;
        updateSearchToggle();
      }

      function updateSearchToggle() {
        const isDark = document.documentElement.classList.contains('dark');
        
        // Desktop Header Toggle
        if (toggleTrack && toggleKnob) {
          if (isSearchActive) {
            toggleTrack.className = `w-[32px] sm:w-[36px] h-[18px] sm:h-[20px] shrink-0 flex flex-row p-[2px] justify-end items-center rounded-[10px] transition-colors duration-200 ${
              isDark ? 'bg-[#FFFFFF]' : 'bg-[#111111]'
            }`;
            toggleKnob.className = `w-[14px] sm:w-[16px] h-[14px] sm:h-[16px] rounded-full transition-transform duration-200 ${
              isDark ? 'bg-[#0A0A0A]' : 'bg-[#FFFFFF]'
            }`;
          } else {
            toggleTrack.className = `w-[32px] sm:w-[36px] h-[18px] sm:h-[20px] shrink-0 flex flex-row p-[2px] justify-start items-center rounded-[10px] transition-colors duration-200 ${
              isDark ? 'bg-[#333333]' : 'bg-neutral-300'
            }`;
            toggleKnob.className = `w-[14px] sm:w-[16px] h-[14px] sm:h-[16px] rounded-full transition-transform duration-200 ${
              isDark ? 'bg-[#B4B4B4]' : 'bg-[#FFFFFF]'
            }`;
          }
        }

        // Mobile Sidebar Toggle
        const mobileTrack = document.getElementById('mobile-search-track');
        const mobileKnob = document.getElementById('mobile-search-knob');
        const mobileStatus = document.getElementById('mobile-search-status-label');
        if (mobileTrack && mobileKnob) {
          if (isSearchActive) {
            mobileTrack.className = `w-[34px] h-[19px] shrink-0 flex flex-row p-[2px] justify-end items-center rounded-[10px] transition-colors duration-200 ${
              isDark ? 'bg-[#FFFFFF]' : 'bg-[#111111]'
            }`;
            mobileKnob.className = `w-[15px] h-[15px] rounded-full transition-transform duration-200 ${
              isDark ? 'bg-[#0A0A0A]' : 'bg-[#FFFFFF]'
            }`;
            if (mobileStatus) {
              mobileStatus.textContent = 'Aktif';
              mobileStatus.className = 'text-[10px] font-medium text-emerald-600 dark:text-emerald-400';
            }
          } else {
            mobileTrack.className = `w-[34px] h-[19px] shrink-0 flex flex-row p-[2px] justify-start items-center rounded-[10px] transition-colors duration-200 ${
              isDark ? 'bg-[#333333]' : 'bg-neutral-300'
            }`;
            mobileKnob.className = `w-[15px] h-[15px] rounded-full transition-transform duration-200 ${
              isDark ? 'bg-[#B4B4B4]' : 'bg-[#FFFFFF]'
            }`;
            if (mobileStatus) {
              mobileStatus.textContent = 'Nonaktif';
              mobileStatus.className = 'text-[10px] text-[#6B7280] dark:text-[#9CA3AF]';
            }
          }
        }
      }

      function toggleAgentMode() {
        isAgentActive = !isAgentActive;
        updateAgentToggle();
      }

      function updateAgentToggle() {
        const isDark = document.documentElement.classList.contains('dark');

        // Desktop Header Toggle
        const desktopTrack = document.getElementById('agent-toggle-track');
        const desktopKnob = document.getElementById('agent-toggle-knob');
        if (desktopTrack && desktopKnob) {
          if (isAgentActive) {
            desktopTrack.className = `w-[32px] sm:w-[36px] h-[18px] sm:h-[20px] shrink-0 flex flex-row p-[2px] justify-end items-center rounded-[10px] transition-colors duration-200 ${
              isDark ? 'bg-[#FFFFFF]' : 'bg-[#111111]'
            }`;
            desktopKnob.className = `w-[14px] sm:w-[16px] h-[14px] sm:h-[16px] rounded-full transition-transform duration-200 ${
              isDark ? 'bg-[#0A0A0A]' : 'bg-[#FFFFFF]'
            }`;
          } else {
            desktopTrack.className = `w-[32px] sm:w-[36px] h-[18px] sm:h-[20px] shrink-0 flex flex-row p-[2px] justify-start items-center rounded-[10px] transition-colors duration-200 ${
              isDark ? 'bg-[#333333]' : 'bg-neutral-300'
            }`;
            desktopKnob.className = `w-[14px] sm:w-[16px] h-[14px] sm:h-[16px] rounded-full transition-transform duration-200 ${
              isDark ? 'bg-[#B4B4B4]' : 'bg-[#FFFFFF]'
            }`;
          }
        }

        // Mobile Sidebar Toggle
        const mobileTrack = document.getElementById('mobile-agent-track');
        const mobileKnob = document.getElementById('mobile-agent-knob');
        const mobileStatus = document.getElementById('mobile-agent-status-label');
        if (mobileTrack && mobileKnob) {
          if (isAgentActive) {
            mobileTrack.className = `w-[34px] h-[19px] shrink-0 flex flex-row p-[2px] justify-end items-center rounded-[10px] transition-colors duration-200 ${
              isDark ? 'bg-[#FFFFFF]' : 'bg-[#111111]'
            }`;
            mobileKnob.className = `w-[15px] h-[15px] rounded-full transition-transform duration-200 ${
              isDark ? 'bg-[#0A0A0A]' : 'bg-[#FFFFFF]'
            }`;
            if (mobileStatus) {
              mobileStatus.textContent = 'Aktif';
              mobileStatus.className = 'text-[10px] font-medium text-purple-600 dark:text-purple-400';
            }
          } else {
            mobileTrack.className = `w-[34px] h-[19px] shrink-0 flex flex-row p-[2px] justify-start items-center rounded-[10px] transition-colors duration-200 ${
              isDark ? 'bg-[#333333]' : 'bg-neutral-300'
            }`;
            mobileKnob.className = `w-[15px] h-[15px] rounded-full transition-transform duration-200 ${
              isDark ? 'bg-[#B4B4B4]' : 'bg-[#FFFFFF]'
            }`;
            if (mobileStatus) {
              mobileStatus.textContent = 'Nonaktif';
              mobileStatus.className = 'text-[10px] text-[#6B7280] dark:text-[#9CA3AF]';
            }
          }
        }
      }

      // Render Session List
      function renderSessions() {
        sessionListEl.innerHTML = '';
        if (sessions.length === 0) {
          sessionListEl.innerHTML = `
            <div class="px-3 py-6 text-center text-[12px] text-[#6B7280] dark:text-[#B4B4B4] select-none">
              No chat history yet
            </div>
          `;
          return;
        }

        sessions.forEach(session => {
          const isActive = session.id === activeSessionId;
          const sessionBtn = document.createElement('div');
          sessionBtn.setAttribute('data-pencil-name', `Session ${session.id}`);
          sessionBtn.className = `w-full h-fit shrink-0 flex flex-row gap-[10px] p-[10px_12px] justify-start items-center rounded-[10px] cursor-pointer transition-colors duration-200 select-none group ${
            isActive
              ? 'bg-[#E8EEFF] dark:bg-[#262626]'
              : 'bg-transparent hover:bg-neutral-200/50 dark:hover:bg-[#1A1A1A]'
          }`;
          sessionBtn.onclick = () => switchSession(session.id);

          sessionBtn.innerHTML = `
            <svg
              data-pencil-name="Chat Icon"
              viewBox="0 0 14 14"
              class="w-[16px] h-[16px] shrink-0 ${
                isActive
                  ? 'text-[#111111] dark:text-[#ECECEC]'
                  : 'text-[#6B7280] dark:text-[#B4B4B4]'
              }"
            >
              <path
                d="M1.97559 1.20313q-0.39307 0.08545-0.71778 0.33837-0.32129 0.24951-0.50244 0.61524-0.12646 0.28027-0.16748 0.58789-0.01367 0.16748 0 5.01074l0 4.17334q0.01367 0.68359 0.02734 0.75537 0.11279 0.33496 0.37256 0.5332 0.25977 0.19482 0.58106 0.19483 0.23584 0 0.46142-0.11279 0.09912-0.04101 0.22217-0.16065 0.12646-0.11963 0.65967-0.63574 0.7417-0.7417 0.82715-0.78613l0.08203-0.04102 8.09375-0.01367 0.16748-0.04102q0.25293-0.07178 0.44092-0.18115 0.18799-0.11279 0.36914-0.29394 0.18457-0.18457 0.29394-0.37256 0.11279-0.18799 0.18457-0.44092l0.04102-0.16748 0.01367-6.29932q-0.01367-0.81348-0.02734-1.08008-0.01367-0.19482-0.04102-0.33496-0.14014-0.4751-0.50586-0.81006-0.3623-0.33838-0.84082-0.4375-0.10938-0.02734-0.81006-0.02734l-4.21435 0-4.17334 0q-0.72803 0.01367-0.8374 0.02734z m9.78564 1.14843q0.30762 0.04102 0.43408 0.32129l0.04102 0.09912 0 7.28028-0.02735 0.08544q-0.12646 0.24951-0.35205 0.33497-0.05469 0.01367-4.10156 0.02734l-4.02978 0.01367-0.15723 0.02735q-0.13672 0.04443-0.26318 0.09912-0.21191 0.08545-0.36573 0.21191-0.15381 0.12305-0.60156 0.58447l-0.58789 0.57422 0.01367-9.23877 0.04102-0.09912q0.05811-0.12646 0.16748-0.21533 0.11279-0.09229 0.23242-0.11279 0.11963-0.02051 4.78174-0.02051 4.66211 0 4.7749 0.02734z"
                fill="currentColor"
              ></path>
            </svg>
            <div
              data-pencil-name="Session Title"
              class="text-[13px] leading-normal flex-1 truncate text-[#111111] dark:text-[#ECECEC] ${
                isActive ? 'font-semibold' : 'font-normal'
              } text-left"
            >
              ${escapeHtml(session.title)}
            </div>
            <button
              type="button"
              class="text-[#6B7280] dark:text-[#B4B4B4] hover:text-red-500 dark:hover:text-red-400 p-1 rounded opacity-0 group-hover:opacity-100 transition cursor-pointer"
              title="Delete chat session"
              onclick="event.stopPropagation(); deleteSession('${session.uuid || session.id}')"
            >
              <svg class="w-[14px] h-[14px] text-[#6B7280] dark:text-[#B4B4B4] hover:text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
              </svg>
          </button>
        `;
        sessionListEl.appendChild(sessionBtn);
      });
    }

    // Helper: Escape HTML
    function escapeHtml(str) {
      if (!str) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    // Helper: Format inline markdown (bold, italic, inline code, strike, link)
    function formatInlineMarkdown(text) {
      if (!text) return '';
      let str = escapeHtml(text);
      // Bold + Italic ***text***
      str = str.replace(/\*\*\*([^\*\n]+?)\*\*\*/g, '<strong class="font-bold italic">$1</strong>');
      // Bold **text** and __text__
      str = str.replace(/\*\*([^\*\n]+?)\*\*/g, '<strong class="font-bold">$1</strong>');
      str = str.replace(/__([^_\n]+?)__/g, '<strong class="font-bold">$1</strong>');
      // Italic *text* or _text_
      str = str.replace(/(^|[^\*])\*([^\*\n]+?)\*(?!\*)/g, '$1<em class="italic">$2</em>');
      str = str.replace(/(^|[^_])_([^_\n]+?)_(?!_)/g, '$1<em class="italic">$2</em>');
      // Inline code `code`
      str = str.replace(/`([^`\n]+?)`/g, '<code class="px-1.5 py-0.5 rounded font-mono text-[11px] bg-neutral-200/80 dark:bg-[#2A2A2E] text-[#D926AA] dark:text-[#F472B6] border border-neutral-300/40 dark:border-neutral-700/50">$1</code>');
      // Strikethrough ~~text~~
      str = str.replace(/~~([^~\n]+?)~~/g, '<del class="line-through opacity-70">$1</del>');
      // Link [text](url)
      str = str.replace(/\[([^\]]+)\]\((https?:\/\/[^\s\)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer" class="underline text-blue-600 dark:text-blue-400 hover:opacity-80">$1</a>');
      return str;
    }

    // Helper: Split table row by pipe
    function splitTableRow(row) {
      let trimmed = row.trim();
      if (trimmed.startsWith('|')) trimmed = trimmed.slice(1);
      if (trimmed.endsWith('|')) trimmed = trimmed.slice(0, -1);
      return trimmed.split('|').map(c => c.trim());
    }

    // Helper: Check if line is a table delimiter (| :--- | :--- |)
    function isTableDelimiter(line) {
      if (!line || !line.includes('-')) return false;
      const trimmed = line.trim();
      return /^\s*\|?\s*:?-{2,}:?\s*(\|\s*:?-{2,}:?\s*)+\|?\s*$/.test(trimmed);
    }

    // Helper: Parse markdown text block containing headings, lists, tables, and paragraphs
    function parseMarkdownBlock(text) {
      const lines = text.split('\n');
      const output = [];
      let i = 0;

      while (i < lines.length) {
        const line = lines[i];
        const nextLine = lines[i + 1];

        // 1. Table Detection
        if (line.includes('|') && isTableDelimiter(nextLine)) {
          const headers = splitTableRow(line);
          const delimiters = splitTableRow(nextLine);
          const alignments = delimiters.map(d => {
            const left = d.startsWith(':');
            const right = d.endsWith(':');
            if (left && right) return 'text-center';
            if (right) return 'text-right';
            return 'text-left';
          });

          i += 2; // skip header and delimiter
          const rows = [];
          while (i < lines.length && lines[i].trim().includes('|') && lines[i].trim() !== '') {
            rows.push(splitTableRow(lines[i]));
            i++;
          }

          let tableHtml = '<div class="my-3.5 w-full overflow-x-auto rounded-xl border border-[#E5E7EB] dark:border-[#2C2C2C] bg-white dark:bg-[#121214] shadow-xs">';
          tableHtml += '<table class="w-full text-xs border-collapse min-w-[360px]">';
          tableHtml += '<thead class="bg-[#F8F9FA] dark:bg-[#1E1E22] border-b border-[#E5E7EB] dark:border-[#2C2C2C] text-[#111111] dark:text-[#F3F4F6]"><tr>';

          headers.forEach((h, colIdx) => {
            const align = alignments[colIdx] || 'text-left';
            tableHtml += `<th class="px-4 py-2.5 font-semibold border-r last:border-r-0 border-[#E5E7EB] dark:border-[#2C2C2C] ${align}">${formatInlineMarkdown(h)}</th>`;
          });

          tableHtml += '</tr></thead><tbody class="divide-y divide-[#E5E7EB] dark:divide-[#2C2C2C]">';

          rows.forEach(r => {
            tableHtml += '<tr class="hover:bg-neutral-50/80 dark:hover:bg-[#1A1A1E] transition-colors odd:bg-transparent even:bg-[#FAFAFA] dark:even:bg-[#161618]">';
            headers.forEach((_, colIdx) => {
              const val = r[colIdx] !== undefined ? r[colIdx] : '';
              const align = alignments[colIdx] || 'text-left';
              tableHtml += `<td class="px-4 py-2.5 border-r last:border-r-0 border-[#E5E7EB] dark:border-[#2C2C2C] text-[#374151] dark:text-[#D1D5DB] ${align}">${formatInlineMarkdown(val)}</td>`;
            });
            tableHtml += '</tr>';
          });

          tableHtml += '</tbody></table></div>';
          output.push(tableHtml);
          continue;
        }

        // 2. Unordered List Items (- or * or •)
        const trimmed = line.trim();
        if (trimmed.startsWith('- ') || trimmed.startsWith('* ') || trimmed.startsWith('• ')) {
          const content = trimmed.substring(2).trim();
          output.push(`<div class="flex items-start gap-2 my-1 pl-1"><span class="text-neutral-400 dark:text-neutral-500 mt-1.5 text-[8px] leading-none shrink-0">●</span><span>${formatInlineMarkdown(content)}</span></div>`);
          i++;
          continue;
        }

        // 3. Numbered List Items (1. )
        const numMatch = trimmed.match(/^(\d+)\.\s+(.*)$/);
        if (numMatch) {
          output.push(`<div class="flex items-start gap-2 my-1 pl-1"><span class="font-semibold text-neutral-500 dark:text-neutral-400 text-xs min-w-[18px] shrink-0">${numMatch[1]}.</span><span>${formatInlineMarkdown(numMatch[2])}</span></div>`);
          i++;
          continue;
        }

        // 4. Headings
        if (trimmed.startsWith('### ')) {
          output.push(`<h3 class="text-sm font-bold text-[#111111] dark:text-[#ECECEC] mt-3 mb-1">${formatInlineMarkdown(trimmed.substring(4))}</h3>`);
          i++;
          continue;
        }
        if (trimmed.startsWith('## ')) {
          output.push(`<h2 class="text-base font-bold text-[#111111] dark:text-[#ECECEC] mt-3.5 mb-1.5">${formatInlineMarkdown(trimmed.substring(3))}</h2>`);
          i++;
          continue;
        }
        if (trimmed.startsWith('# ')) {
          output.push(`<h1 class="text-lg font-bold text-[#111111] dark:text-[#ECECEC] mt-4 mb-2">${formatInlineMarkdown(trimmed.substring(2))}</h1>`);
          i++;
          continue;
        }

        // 5. Blank line spacing
        if (trimmed === '') {
          output.push('<div class="h-2"></div>');
          i++;
          continue;
        }

        // 6. Regular Line with inline markdown (e.g. bold, italic, code)
        output.push(`<div>${formatInlineMarkdown(line)}</div>`);
        i++;
      }

      return output.join('');
    }

    // Helper: Format message content with code blocks, tables, bold, and copy buttons
    function formatMessageBody(content, isUser = false) {
      if (!content) return '';
      if (isUser) {
        const lines = content.split('\n');
        return lines.map(l => formatInlineMarkdown(l)).join('<br />');
      }

      const codeBlockRegex = /```([a-zA-Z0-9_\-\.]*)\n([\s\S]*?)```/g;
      const parts = [];
      let lastIndex = 0;
      let match;

      while ((match = codeBlockRegex.exec(content)) !== null) {
        if (match.index > lastIndex) {
          const textChunk = content.substring(lastIndex, match.index);
          parts.push(parseMarkdownBlock(textChunk));
        }
        const lang = match[1] || 'code';
        const code = escapeHtml(match[2].trim());
        parts.push(`
          <div class="my-2.5 w-full rounded-xl overflow-hidden border border-[#E5E7EB] dark:border-[#2C2C2C] bg-[#1A1A1E] text-neutral-100 text-xs shadow-sm">
            <div class="flex items-center justify-between px-3 py-1.5 bg-[#141418] border-b border-[#2C2C2C] text-[11px] text-neutral-400 font-mono select-none">
              <span class="font-semibold text-neutral-300">${escapeHtml(lang)}</span>
              <button type="button" class="hover:text-white transition flex items-center gap-1 cursor-pointer" onclick="copyCode(this)">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span>Salin</span>
              </button>
            </div>
            <pre class="p-3 overflow-x-auto font-mono text-[12px] leading-relaxed select-text whitespace-pre"><code>${code}</code></pre>
          </div>
        `);
        lastIndex = match.index + match[0].length;
      }

      if (lastIndex < content.length) {
        parts.push(parseMarkdownBlock(content.substring(lastIndex)));
      }

      return parts.join('');
    }

    function copyCode(btn) {
      const pre = btn.closest('.my-2.5')?.querySelector('code');
      if (pre) {
        navigator.clipboard.writeText(pre.textContent).then(() => {
          const span = btn.querySelector('span');
          if (span) {
            const old = span.textContent;
            span.textContent = 'Tersalin!';
            setTimeout(() => { span.textContent = old; }, 2000);
          }
        });
      }
    }

    // Load and Render Models from Tokenportal
    async function loadModelsFromBackend() {
      try {
        const res = await fetch('{{ route('chat.models') }}');
        if (res.ok) {
          const json = await res.json();
          if (json.data && Array.isArray(json.data) && json.data.length > 0) {
            availableModels = json.data;
            const defaultModelId = json.default || 'kimi-k27-code';
            const currentValid = availableModels.some(m => m.id === activeModel);
            if (!currentValid) {
              activeModel = defaultModelId;
            }
            selectModel(activeModel, false);
          }
        }
      } catch (err) {
        console.error('Failed to load Tokenportal models:', err);
      }
      renderModelDropdown();
      renderMobileModelModalList();
    }

    function renderModelDropdown(filterText = '') {
      if (!modelDropdownList) return;
      const filter = filterText.toLowerCase().trim();
      const filtered = availableModels.filter(m => 
        m.id.toLowerCase().includes(filter) || 
        m.name.toLowerCase().includes(filter) || 
        (m.owned_by && m.owned_by.toLowerCase().includes(filter))
      );

      if (modelCountLabel) {
        modelCountLabel.textContent = `${filtered.length} model`;
      }

      if (filtered.length === 0) {
        modelDropdownList.innerHTML = `
          <div class="p-4 text-center text-xs text-[#6B7280] dark:text-[#9CA3AF]">
            Tidak ada model "${escapeHtml(filterText)}"
          </div>
        `;
        return;
      }

      modelDropdownList.innerHTML = '';
      filtered.forEach(m => {
        const isSelected = m.id === activeModel;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = `w-full flex items-center justify-between px-3 py-2 text-left transition hover:bg-neutral-50 dark:hover:bg-[#222222] cursor-pointer ${
          isSelected ? 'bg-neutral-100/70 dark:bg-[#252525]' : ''
        }`;
        btn.onclick = () => selectModel(m.id);

        let pills = '';
        if (m.supports_reasoning) {
          pills += `<span class="text-[9px] px-1.5 py-0.5 rounded font-medium bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">Reasoning</span>`;
        }
        if (m.context_window) {
          const ctxK = m.context_window >= 1000000 
            ? `${(m.context_window / 1000000).toFixed(0)}M` 
            : `${Math.round(m.context_window / 1000)}k`;
          pills += `<span class="text-[9px] px-1.5 py-0.5 rounded font-mono bg-neutral-100 dark:bg-[#2c2c2c] text-[#6B7280] dark:text-[#9CA3AF]">${ctxK} ctx</span>`;
        }

        btn.innerHTML = `
          <div class="min-w-0 pr-2">
            <div class="flex items-center gap-1.5 flex-wrap">
              <span class="font-semibold text-xs text-[#111111] dark:text-[#ECECEC] truncate">${escapeHtml(m.name)}</span>
              ${pills}
            </div>
            <div class="text-[10px] text-[#6B7280] dark:text-[#9CA3AF] truncate mt-0.5">
              <span class="font-medium text-[#4B5563] dark:text-[#D1D5DB]">${escapeHtml(m.owned_by || 'Tokenportal')}</span> • ${escapeHtml(m.id)}
            </div>
          </div>
          <div class="shrink-0 text-xs font-bold text-neutral-800 dark:text-neutral-200 ${isSelected ? '' : 'invisible'}">
            ✓
          </div>
        `;
        modelDropdownList.appendChild(btn);
      });
    }

    // Render Model List inside Mobile Modal
    function renderMobileModelModalList(filterText = '') {
      if (!mobileModelList) return;
      const filter = filterText.toLowerCase().trim();
      const filtered = availableModels.filter(m => 
        m.id.toLowerCase().includes(filter) || 
        m.name.toLowerCase().includes(filter) || 
        (m.owned_by && m.owned_by.toLowerCase().includes(filter))
      );

      if (mobileModelCountLabel) {
        mobileModelCountLabel.textContent = `${filtered.length} model`;
      }

      if (filtered.length === 0) {
        mobileModelList.innerHTML = `
          <div class="py-10 text-center text-xs text-[#6B7280] dark:text-[#9CA3AF]">
            Tidak ada model yang cocok dengan "${escapeHtml(filterText)}"
          </div>
        `;
        return;
      }

      mobileModelList.innerHTML = '';
      filtered.forEach(m => {
        const isSelected = m.id === activeModel;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = `w-full flex items-center justify-between p-3 rounded-xl text-left transition-all active:scale-[0.99] cursor-pointer ${
          isSelected 
            ? 'bg-neutral-100 dark:bg-[#252528] ring-1 ring-neutral-300 dark:ring-neutral-700' 
            : 'hover:bg-neutral-50 dark:hover:bg-[#1E1E20] border border-transparent'
        }`;
        btn.onclick = () => selectModel(m.id);

        let pills = '';
        if (m.supports_reasoning) {
          pills += `<span class="text-[9px] px-1.5 py-0.5 rounded font-medium bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">Reasoning</span>`;
        }
        if (m.context_window) {
          const ctxK = m.context_window >= 1000000 
            ? `${(m.context_window / 1000000).toFixed(0)}M` 
            : `${Math.round(m.context_window / 1000)}k`;
          pills += `<span class="text-[9px] px-1.5 py-0.5 rounded font-mono bg-neutral-200/70 dark:bg-[#2c2c30] text-[#4B5563] dark:text-[#A1A1AA]">${ctxK} ctx</span>`;
        }

        btn.innerHTML = `
          <div class="min-w-0 pr-3 flex items-start gap-2.5 flex-1">
            <div class="mt-0.5 w-4 h-4 rounded-full flex items-center justify-center shrink-0 border transition-colors ${
              isSelected
                ? 'border-[#111111] dark:border-[#ECECEC] bg-[#111111] dark:bg-[#ECECEC] text-white dark:text-[#111111]'
                : 'border-neutral-300 dark:border-neutral-600'
            }">
              ${isSelected ? '<svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>' : ''}
            </div>
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-1.5 flex-wrap">
                <span class="font-semibold text-xs text-[#111111] dark:text-[#ECECEC]">${escapeHtml(m.name)}</span>
                ${pills}
              </div>
              <div class="text-[11px] text-[#6B7280] dark:text-[#9CA3AF] mt-0.5 flex items-center gap-1.5 flex-wrap">
                <span class="font-medium text-[#4B5563] dark:text-[#D1D5DB]">${escapeHtml(m.owned_by || 'Tokenportal')}</span>
                <span>•</span>
                <span class="truncate font-mono text-[10px] text-[#6B7280] dark:text-[#888888]">${escapeHtml(m.id)}</span>
              </div>
            </div>
          </div>
        `;
        mobileModelList.appendChild(btn);
      });
    }

    function openMobileModelModal() {
      if (!mobileModelModal) return;
      mobileModelModal.classList.remove('hidden');
      renderMobileModelModalList(mobileModelSearchInput ? mobileModelSearchInput.value : '');
      if (mobileModelSearchInput) {
        setTimeout(() => mobileModelSearchInput.focus(), 120);
      }
    }

    function closeMobileModelModal() {
      if (!mobileModelModal) return;
      mobileModelModal.classList.add('hidden');
    }

    function selectModel(modelId, updateSession = true) {
      activeModel = modelId;
      const modelObj = availableModels.find(m => m.id === modelId);
      const displayName = modelObj ? modelObj.name : modelId;
      const provider = modelObj ? modelObj.owned_by : 'Moonshot';

      // Desktop dropdown elements
      const nameEl = document.getElementById('model-display-name');
      if (nameEl) nameEl.textContent = displayName;

      if (modelProviderBadge) {
        modelProviderBadge.textContent = provider;
        modelProviderBadge.classList.remove('hidden');
      }

      if (modelDropdownMenu) {
        modelDropdownMenu.classList.add('hidden');
        if (modelDropdownArrow) modelDropdownArrow.classList.remove('rotate-180');
      }

      // Mobile sidebar card elements
      if (mobileModelDisplayName) {
        mobileModelDisplayName.textContent = displayName;
      }
      if (mobileModelProviderBadge) {
        mobileModelProviderBadge.textContent = provider;
      }
      if (mobileModalActiveModelName) {
        mobileModalActiveModelName.textContent = displayName;
      }

      // Close mobile model modal if open
      closeMobileModelModal();

      if (updateSession) {
        const session = sessions.find(s => s.id === activeSessionId || s.uuid === activeSessionId);
        if (session) {
          session.model = modelId;
        }
      }

      renderModelDropdown(modelSearchInput ? modelSearchInput.value : '');
      renderMobileModelModalList(mobileModelSearchInput ? mobileModelSearchInput.value : '');
    }

    // Render Messages
    function renderMessages() {
      const session = sessions.find(s => s.id === activeSessionId || s.uuid === activeSessionId);
      chatColumnEl.innerHTML = '';
      if (!session || !session.messages.length) {
        chatColumnEl.innerHTML = `
          <div class="w-full py-16 flex flex-col items-center justify-center text-center gap-3">
            <div class="w-12 h-12 bg-neutral-100 dark:bg-[#1A1A1A] rounded-full flex items-center justify-center text-neutral-800 dark:text-neutral-200">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a.75.75 0 0 1-.974-.94 4.004 4.004 0 0 0 .551-1.92A8.932 8.932 0 0 1 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
              </svg>
            </div>
            <h3 class="text-base font-semibold text-[#111111] dark:text-[#ECECEC]">Start a new conversation</h3>
            <p class="text-sm text-[#6B7280] dark:text-[#B4B4B4] max-w-sm">
              Tanyakan apa saja ke model <strong>${escapeHtml(activeModel)}</strong> via Tokenportal provider.
            </p>
          </div>
        `;
        return;
      }

      session.messages.forEach(msg => {
        if (msg.sender === 'ai' || msg.role === 'assistant') {
          const aiRow = document.createElement('div');
          aiRow.className = 'w-full h-fit shrink-0 flex flex-row gap-2.5 sm:gap-3.5 justify-start items-start animate-fade-in mb-3';
          
          if (msg.isThinking) {
            aiRow.innerHTML = `
              <div data-pencil-name="AI Avatar" class="box-border w-7 h-7 sm:w-8 sm:h-8 shrink-0 bg-[#111111] dark:bg-[#ECECEC] rounded-full flex items-center justify-center mt-0.5 shadow-xs transition-colors duration-200">
                <div class="w-1.5 h-1.5 bg-white dark:bg-[#111111] rounded-full animate-ping"></div>
              </div>
              <div class="box-border w-fit max-w-[calc(100%-38px)] sm:max-w-[560px] shrink-0 h-fit flex flex-row items-center gap-2 p-3 sm:p-3.5 bg-[#F3F4F6] dark:bg-[#18181B] rounded-2xl rounded-tl-sm text-xs text-[#6B7280] dark:text-[#A0A0A0] shadow-xs">
                <span>Menghasilkan respon dengan ${escapeHtml(session.model || activeModel)}...</span>
                <div class="flex items-center gap-1 ml-1">
                  <div class="w-1.5 h-1.5 rounded-full bg-neutral-400 dark:bg-neutral-500 animate-bounce"></div>
                  <div class="w-1.5 h-1.5 rounded-full bg-neutral-400 dark:bg-neutral-500 animate-bounce [animation-delay:0.2s]"></div>
                  <div class="w-1.5 h-1.5 rounded-full bg-neutral-400 dark:bg-neutral-500 animate-bounce [animation-delay:0.4s]"></div>
                </div>
              </div>
            `;
            chatColumnEl.appendChild(aiRow);
            return;
          }

          let bubbleContent = '';
          if (msg.subtitle) {
            bubbleContent += `<div class="text-[14px] leading-normal w-full text-[#111111] dark:text-[#ECECEC] font-semibold text-left mb-1.5">${escapeHtml(msg.subtitle)}</div>`;
          }

          // Reasoning Section (e.g. from Moonshot kimi-k27-code)
          if (msg.reasoning) {
            bubbleContent += `
              <details class="w-full mb-3 rounded-xl border border-[#E5E7EB] dark:border-[#2C2C2C] bg-white/70 dark:bg-[#111111]/70 overflow-hidden text-xs">
                <summary class="cursor-pointer px-3 py-2 font-medium text-[#4B5563] dark:text-[#9CA3AF] flex items-center justify-between hover:bg-neutral-50 dark:hover:bg-[#1e1e1e] select-none">
                  <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.002 6.002 0 0 0-4-5.659V5.25a2.25 2.25 0 0 1 4.5 0v1.841a6.002 6.002 0 0 0 4 5.659M12 12.75h.008v.008H12v-.008Z"/></svg>
                    <strong>Penalaran / Thinking Process</strong>
                  </span>
                  <span class="text-[10px] text-[#9CA3AF]">Klik untuk buka</span>
                </summary>
                <div class="p-3 border-t border-[#E5E7EB] dark:border-[#2C2C2C] text-[#374151] dark:text-[#D1D5DB] font-mono text-[11px] leading-relaxed whitespace-pre-wrap bg-[#FAFAFA] dark:bg-[#0E0E0E]">
                  ${escapeHtml(msg.reasoning)}
                </div>
              </details>
            `;
          }

          const formattedBody = formatMessageBody(msg.content);
          bubbleContent += `<div class="text-[14px] leading-[22px] w-full text-[#111111] dark:text-[#ECECEC] font-normal text-left">${formattedBody}</div>`;

          // Footer with model and tokens info
          let footerMeta = '';
          if (msg.tokens) {
            footerMeta += `<span>${msg.tokens} tokens</span>`;
          }
          if (session.model) {
            footerMeta += `${footerMeta ? ' • ' : ''}<span>${escapeHtml(session.model)}</span>`;
          }
          if (footerMeta) {
            bubbleContent += `
              <div class="w-full mt-2 pt-2 border-t border-[#E5E7EB]/60 dark:border-[#262626] flex items-center justify-between text-[11px] text-[#9CA3AF]">
                <div>${footerMeta}</div>
              </div>
            `;
          }

          aiRow.innerHTML = `
            <div data-pencil-name="AI Avatar" class="box-border w-7 h-7 sm:w-8 sm:h-8 shrink-0 bg-[#111111] dark:bg-[#ECECEC] rounded-full flex items-center justify-center mt-0.5 shadow-xs transition-colors duration-200">
              <div class="w-1.5 h-1.5 bg-white dark:bg-[#111111] rounded-full opacity-90"></div>
            </div>
            <div class="box-border w-fit max-w-[calc(100%-38px)] sm:max-w-[760px] flex-1 flex flex-col p-3.5 sm:p-4 bg-[#F3F4F6] dark:bg-[#18181B] rounded-2xl rounded-tl-sm text-[#111111] dark:text-[#ECECEC] shadow-xs transition-colors duration-200 break-words">
              ${bubbleContent}
            </div>
          `;
          chatColumnEl.appendChild(aiRow);
        } else {
          const userRow = document.createElement('div');
          userRow.className = 'w-full h-fit shrink-0 flex flex-row justify-end items-start animate-fade-in mb-3';
          const formattedBody = formatMessageBody(msg.content, true);
          userRow.innerHTML = `
            <div class="box-border w-fit max-w-[85%] sm:max-w-[560px] shrink-0 h-fit flex flex-col p-3 sm:p-3.5 bg-[#111111] dark:bg-[#FFFFFF] rounded-2xl rounded-tr-sm text-[#FFFFFF] dark:text-[#111111] shadow-xs transition-colors duration-200 break-words">
              <div class="text-[14px] leading-[21px] w-full text-[#FFFFFF] dark:text-[#111111] font-normal text-left">${formattedBody}</div>
            </div>
          `;
          chatColumnEl.appendChild(userRow);
        }
      });

      scrollToBottom();
    }

    function scrollToBottom(smooth = false) {
      setTimeout(() => {
        if (smooth) {
          chatScrollEl.scrollTo({ top: chatScrollEl.scrollHeight, behavior: 'smooth' });
        } else {
          chatScrollEl.scrollTop = chatScrollEl.scrollHeight;
        }
      }, 50);
    }

    function scrollToBottomSmooth() {
      chatScrollEl.scrollTo({ top: chatScrollEl.scrollHeight, behavior: 'smooth' });
    }

    function switchSession(id) {
      activeSessionId = id;
      const session = sessions.find(s => s.id === id || s.uuid === id);
      if (session && session.model) {
        selectModel(session.model, false);
      }
      renderSessions();
      renderMessages();
      closeMobileSidebar();
    }

    async function loadSessionsFromBackend() {
      try {
        const res = await fetch('{{ route('chat.sessions.index') }}');
        if (res.ok) {
          const data = await res.json();
          sessions.length = 0;
          data.forEach(item => {
            sessions.push({
              id: item.uuid,
              uuid: item.uuid,
              title: item.title,
              model: item.model || 'kimi-k27-code',
              messages: (item.messages || []).map(m => ({
                sender: m.role === 'assistant' ? 'ai' : m.role,
                role: m.role,
                content: m.content,
                reasoning: m.reasoning,
                tokens: m.tokens
              }))
            });
          });
          renderSessions();
          if (sessions.length > 0) {
            switchSession(sessions[0].id);
          } else {
            renderMessages();
          }
        }
      } catch (err) {
        console.error('Failed to load chat sessions:', err);
        renderSessions();
        renderMessages();
      }
    }

    async function deleteSession(id) {
      if (!confirm('Apakah Anda yakin ingin menghapus sesi chat ini?')) return;
      try {
        const res = await fetch(`/chat/sessions/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          }
        });
        if (res.ok) {
          const idx = sessions.findIndex(s => s.id === id || s.uuid === id);
          if (idx !== -1) {
            sessions.splice(idx, 1);
          }
          if (activeSessionId === id) {
            activeSessionId = sessions.length ? (sessions[0].uuid || sessions[0].id) : null;
          }
          renderSessions();
          renderMessages();
        }
      } catch (err) {
        console.error('Failed to delete chat session:', err);
      }
    }

    function startNewChat() {
      activeSessionId = null;
      renderSessions();
      renderMessages();
      messageInput.focus();
      closeMobileSidebar();
    }

    async function handleSendMessage(e) {
      e.preventDefault();
      const text = messageInput.value.trim();
      if (!text) return;

      let session = sessions.find(s => s.id === activeSessionId || s.uuid === activeSessionId);
      const isNewSession = !session || !session.uuid;

      if (!session) {
        const tempId = 'temp-' + Date.now();
        session = {
          id: tempId,
          uuid: null,
          title: text.length > 25 ? text.substring(0, 25) + '...' : text,
          model: activeModel,
          messages: []
        };
        sessions.unshift(session);
        activeSessionId = tempId;
      }

      // Add User Message to UI
      const userMsg = {
        sender: 'user',
        content: text
      };
      session.messages.push(userMsg);

      // Add Thinking Indicator to UI
      const thinkingMsg = {
        sender: 'ai',
        isThinking: true
      };
      session.messages.push(thinkingMsg);

      messageInput.value = '';
      if (messageInput.style) {
        messageInput.style.height = 'auto';
      }
      renderSessions();
      renderMessages();

      try {
        if (!session.uuid) {
          // Create session and generate AI completion in one request
          const res = await fetch('{{ route('chat.sessions.store') }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              title: session.title,
              model: activeModel,
              platform: 'web',
              generate_reply: true,
              search_mode: isSearchActive,
              agent_mode: isAgentActive,
              messages: [{
                role: 'user',
                content: text
              }]
            })
          });

          // Remove thinking indicator
          const thinkIdx = session.messages.indexOf(thinkingMsg);
          if (thinkIdx !== -1) session.messages.splice(thinkIdx, 1);

          if (res.ok) {
            const data = await res.json();
            session.id = data.uuid;
            session.uuid = data.uuid;
            session.title = data.title;
            session.model = data.model;
            activeSessionId = data.uuid;

            session.messages = (data.messages || []).map(m => ({
              sender: m.role === 'assistant' ? 'ai' : m.role,
              role: m.role,
              content: m.content,
              reasoning: m.reasoning,
              tokens: m.tokens
            }));
          } else {
            const errData = await res.json().catch(() => ({}));
            session.messages.push({
              sender: 'ai',
              content: `⚠️ Maaf, gagal mendapatkan respon dari Tokenportal (${res.status}): ${errData.message || 'Silakan periksa API key atau koneksi internet.'}`
            });
          }
        } else {
          // Existing session: send message and get completion from Tokenportal
          const res = await fetch(`/chat/sessions/${session.uuid}/send`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              content: text,
              model: activeModel,
              search_mode: isSearchActive,
              agent_mode: isAgentActive
            })
          });

          // Remove thinking indicator
          const thinkIdx = session.messages.indexOf(thinkingMsg);
          if (thinkIdx !== -1) session.messages.splice(thinkIdx, 1);

          if (res.ok) {
            const data = await res.json();
            if (data.assistant_message) {
              session.messages.push({
                sender: 'ai',
                role: 'assistant',
                content: data.assistant_message.content,
                reasoning: data.assistant_message.reasoning,
                tokens: data.assistant_message.tokens
              });
            }
            if (data.conversation && data.conversation.title) {
              session.title = data.conversation.title;
            }
          } else {
            const errData = await res.json().catch(() => ({}));
            session.messages.push({
              sender: 'ai',
              content: `⚠️ Gagal menghasilkan respon (${res.status}): ${errData.message || 'Silakan coba lagi.'}`
            });
          }
        }
      } catch (err) {
        console.error('Request failed:', err);
        const thinkIdx = session.messages.indexOf(thinkingMsg);
        if (thinkIdx !== -1) session.messages.splice(thinkIdx, 1);
        session.messages.push({
          sender: 'ai',
          content: `⚠️ Terjadi kesalahan koneksi: ${err.message}`
        });
      } finally {
        renderSessions();
        renderMessages();
      }
    }

    // Event Listeners
    function autoResizeTextarea() {
      if (!messageInput) return;
      messageInput.style.height = 'auto';
      messageInput.style.height = Math.min(messageInput.scrollHeight, 144) + 'px';
    }

    if (messageInput) {
      messageInput.addEventListener('input', autoResizeTextarea);

      messageInput.addEventListener('keydown', (e) => {
        // Shift + Enter: trigger enter (kebawah / newline)
        if (e.key === 'Enter' && e.shiftKey) {
          // Default behavior inserts newline into textarea; resize height on next frame
          setTimeout(autoResizeTextarea, 0);
          return;
        }

        // Enter without Shift: trigger submit / send message
        if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
          e.preventDefault();
          const chatForm = document.getElementById('chat-form');
          if (chatForm) {
            chatForm.requestSubmit();
          } else {
            handleSendMessage(e);
          }
        }
      });
    }

    if (searchToggleBtn) {
      searchToggleBtn.addEventListener('click', () => {
        toggleSearchMode();
      });
    }

    if (agentToggleBtn) {
      agentToggleBtn.addEventListener('click', () => {
        toggleAgentMode();
      });
    }

    // Model Dropdown
    if (modelDropdownBtn) {
      modelDropdownBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = !modelDropdownMenu.classList.contains('hidden');
        if (isOpen) {
          modelDropdownMenu.classList.add('hidden');
          modelDropdownArrow.classList.remove('rotate-180');
        } else {
          modelDropdownMenu.classList.remove('hidden');
          modelDropdownArrow.classList.add('rotate-180');
          userMenuDropdown.classList.add('hidden');
          if (modelSearchInput) {
            modelSearchInput.focus();
          }
        }
      });
    }

    // Model Search filter (Desktop)
    if (modelSearchInput) {
      modelSearchInput.addEventListener('input', (e) => {
        renderModelDropdown(e.target.value);
      });
      modelSearchInput.addEventListener('click', (e) => {
        e.stopPropagation();
      });
    }

    // Mobile Model Search filter (Modal)
    if (mobileModelSearchInput) {
      mobileModelSearchInput.addEventListener('input', (e) => {
        renderMobileModelModalList(e.target.value);
      });
      mobileModelSearchInput.addEventListener('click', (e) => {
        e.stopPropagation();
      });
    }

    // User Menu
    if (userMenuBtn) {
      userMenuBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        userMenuDropdown.classList.toggle('hidden');
        if (modelDropdownMenu) modelDropdownMenu.classList.add('hidden');
      });
    }

    // Close dropdowns and modal on outside click
    document.addEventListener('click', (e) => {
      if (modelDropdownBtn && modelDropdownMenu && !modelDropdownBtn.contains(e.target) && !modelDropdownMenu.contains(e.target)) {
        modelDropdownMenu.classList.add('hidden');
        if (modelDropdownArrow) modelDropdownArrow.classList.remove('rotate-180');
      }
      if (userMenuBtn && userMenuDropdown && !userMenuBtn.contains(e.target) && !userMenuDropdown.contains(e.target)) {
        userMenuDropdown.classList.add('hidden');
      }
    });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        closeMobileModelModal();
        if (modelDropdownMenu) {
          modelDropdownMenu.classList.add('hidden');
          if (modelDropdownArrow) modelDropdownArrow.classList.remove('rotate-180');
        }
        if (userMenuDropdown) {
          userMenuDropdown.classList.add('hidden');
        }
      }
    });

    // Mobile Sidebar
    function openMobileSidebar() {
      sidebarEl.classList.remove('-translate-x-full');
      sidebarBackdrop.classList.remove('hidden');
    }

    function closeMobileSidebar() {
      sidebarEl.classList.add('-translate-x-full');
      sidebarBackdrop.classList.add('hidden');
    }

    if (mobileSidebarToggle) {
      mobileSidebarToggle.addEventListener('click', openMobileSidebar);
    }
    if (sidebarBackdrop) {
      sidebarBackdrop.addEventListener('click', closeMobileSidebar);
    }

    // Scroll to bottom floating button trigger
    const scrollBottomBtn = document.getElementById('scroll-bottom-btn');
    if (chatScrollEl && scrollBottomBtn) {
      chatScrollEl.addEventListener('scroll', () => {
        const distFromBottom = chatScrollEl.scrollHeight - chatScrollEl.scrollTop - chatScrollEl.clientHeight;
        if (distFromBottom > 160) {
          scrollBottomBtn.classList.remove('hidden');
        } else {
          scrollBottomBtn.classList.add('hidden');
        }
      });
    }

    // Initialize
    updateSearchToggle();
    updateAgentToggle();
    loadModelsFromBackend();
    loadSessionsFromBackend();
  </script>
</body>
</html>
