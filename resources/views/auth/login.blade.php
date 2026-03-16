<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('login.login_btn') }} - Audit Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full">

        <!-- Brand / Logo -->
        <div class="flex flex-col items-center mb-10 text-center">
            <div class="flex h-20 w-20 items-center justify-center rounded-xl flex-shrink-0">
                <img src="https://edo.adm.uz/assets/logo3.18831604.png" alt="ADM GLOBAL">
            </div>
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">{{ __('login.welcome') }}</h1>
            <p class="text-sm font-medium text-slate-500 mt-2">{{ __('login.subtitle') }}</p>
        </div>

        <!-- Card -->
        <div class="bg-white border border-slate-200 rounded-3xl p-8 shadow-2xl shadow-slate-200/50">
            @if($errors->any())
                <div class="mb-6 px-4 py-3 rounded-2xl bg-rose-50 border border-rose-100 text-xs font-bold text-rose-600 flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-6">
                @csrf
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">{{ __('login.username') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <input type="text" name="username" required
                            class="w-full rounded-2xl bg-slate-50 border border-slate-200 pl-12 pr-4 py-4 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all font-medium"
                            placeholder="{{ __('login.username') }}">
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between px-1">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest">{{ __('login.password') }}</label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input type="password" name="password" required
                            class="w-full rounded-2xl bg-slate-50 border border-slate-200 pl-12 pr-4 py-4 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all"
                            placeholder="••••••••">
                    </div>
                </div>

                <!-- <div class="flex items-center justify-between px-1">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        <span class="text-xs font-bold text-slate-500 group-hover:text-slate-700 transition-colors">{{ __('login.remember_me') }}</span>
                    </label>
                    <a href="#" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">{{ __('login.forgot_password') }}</a>
                </div> -->

                <button type="submit"
                    class="w-full rounded-2xl bg-indigo-600 py-4 text-sm font-bold text-white hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-600/20 active:scale-[0.98] mt-2 tracking-wide uppercase">
                    {{ __('login.login_btn') }}
                </button>
            </form>
        </div>

        <p class="text-center mt-10 text-sm font-bold text-slate-400 uppercase tracking-widest">
            © 2026 Audit Management by ADM GLOBAL
        </p>
    </div>

</body>
</html>
