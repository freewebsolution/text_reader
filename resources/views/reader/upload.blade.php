<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Image Reader') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-2xl font-semibold text-gray-800">{{ __("Converti l'immagine in testo") }}</h3>

                    <!-- Form per il caricamento dell'immagine -->
                    <form action="{{ route('reader.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        
                        <!-- Sezione immagine -->
                        <div>
                            <label for="image" class="block text-lg font-medium text-gray-700">{{ __("Scegli un'immagine") }}</label>
                            <input type="file" name="image" id="image" class="mt-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm py-2 px-4" required>
                            @error('image')
                                <span class="text-red-500 text-sm mt-2">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <!-- Bottone invio -->
                        <button type="submit" class="w-full py-3 px-4 bg-indigo-600 text-white font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            {{ __('Carica immagine') }}
                        </button>
                    </form>

                    @if (isset($image))
                        <div class="mt-8 bg-gray-50 p-4 rounded-lg shadow-md">
                            <h4 class="text-lg font-semibold">{{ __('Anteprima testo estratto dall\'immagine:') }}</h4>
                            <p class="text-gray-800">{{ $image }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
