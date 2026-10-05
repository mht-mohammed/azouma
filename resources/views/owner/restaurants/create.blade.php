<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">إنشاء مطعم</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('owner.restaurants.store') }}" class="p-6">
                    @csrf
                    @include('owner.restaurants.partials.fields')
                    <div class="mt-6">
                        <x-primary-button>إنشاء (سيكون قيد المراجعة)</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
