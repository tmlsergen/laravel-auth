@extends('layout.skeleton')

@section('content')
    <div class="p-6 rounded-lg bg-[#1e252b]">
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-white">Active Sessions</h2>
            <p class="mt-1 text-sm text-[#8b949e]">Your session information across all devices</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-separate border-spacing-0">
                <thead>
                    <tr class="border-b border-[#30363d]">
                        <th class="pb-4 text-left text-xs font-medium text-[#8b949e] uppercase">
                            IP Address
                        </th>
                        <th class="pb-4 text-left text-xs font-medium text-[#8b949e] uppercase">
                            Browser
                        </th>
                        <th class="pb-4 text-left text-xs font-medium text-[#8b949e] uppercase">
                            Last Activity
                        </th>
                        <th class="pb-4 text-right text-xs font-medium text-[#8b949e] uppercase">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sessions as $session)
                        <tr class="border-b border-[#30363d]">
                            <td class="py-4 text-sm text-[#c9d1d9] font-medium">
                                {{ $session->ip_address }}
                            </td>
                            <td class="py-4 text-sm text-[#8b949e]">
                                <div class="max-w-lg break-words">
                                    {{ $session->user_agent }}
                                </div>
                            </td>
                            <td class="py-4 text-sm text-[#8b949e]">
                                {{ \Carbon\Carbon::parse($session->last_activity)->diffForHumans() }}
                            </td>
                            <td class="py-4 text-right">
                                <form action="{{ route('user.me.sessions.logout', ['session_id' => $session->id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 text-sm text-[#8b949e] hover:text-white transition-colors">
                                        Terminate
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
