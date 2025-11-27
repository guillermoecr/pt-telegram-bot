<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Conversación con') }} {{ $conversation->chat->first_name ?? $conversation->chat->username }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <a href="{{ route('conversations.index') }}" class="text-indigo-600 hover:text-indigo-900 mb-4 inline-block">
                        &larr; Volver al Listado
                    </a>

                    <h3 class="text-xl font-bold mb-4 border-b pb-2">
                        Chat ID: <span class="text-indigo-600">{{ $conversation->chat->telegram_id }}</span>
                    </h3>

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif
                    
                    {{-- Chat Bubble UI --}}
                    <div id="messages" class="h-96 overflow-y-scroll border p-4 mb-6 bg-gray-50 rounded-lg flex flex-col space-y-4">
                        @forelse ($messages as $message)
                            {{-- Mensaje Saliente (Admin/Bot) --}}
                            @if ($message->type == 'outbound')
                                <div class="flex justify-end">
                                    <div class="bg-indigo-500 text-white rounded-lg p-3 max-w-xs shadow-md">
                                        <p class="text-xs font-semibold">Tú (Admin)</p>
                                        <p>{{ $message->text }}</p>
                                        <span class="text-xs block text-right mt-1 opacity-75">{{ $message->created_at->format('H:i') }}</span>
                                    </div>
                                </div>
                            {{-- Mensaje Entrante (Usuario Telegram) --}}
                            @else
                                <div class="flex justify-start">
                                    <div class="bg-white border border-gray-200 rounded-lg p-3 max-w-xs shadow-md">
                                        <p class="text-xs font-semibold text-indigo-600">{{ $conversation->chat->first_name ?? $conversation->chat->username }}</p>
                                        <p class="text-gray-800">{{ $message->text }}</p>
                                        <span class="text-xs block text-right mt-1 text-gray-500">{{ $message->created_at->format('H:i') }}</span>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <p class="text-center text-gray-500 italic">No hay mensajes aún.</p>
                        @endforelse
                    </div>

                    {{-- Formulario de Respuesta --}}
                    <h4 class="text-lg font-bold mb-2">Enviar Respuesta</h4>

                    <form action="{{ route('conversations.send', $conversation) }}" method="POST">
                        @csrf
                        <textarea 
                            name="text" 
                            rows="3" 
                            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm resize-none @error('text') border-red-500 @enderror" 
                            placeholder="Escribe tu mensaje aquí..."
                            required
                        >{{ old('text') }}</textarea>

                        @error('text')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror

                        <button type="submit" class="mt-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow-md transition duration-150">
                            Enviar Mensaje a Telegram
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>