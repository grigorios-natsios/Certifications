{{-- Shared form fields for Clients Create + Edit --}}

<div class="form-section">
    <div class="form-section-head">
        <h2 class="section-title">{{ __('Στοιχεία πελάτη') }}</h2>
        <p class="text-xs text-slate-500 mt-0.5">{{ __('Βασικές πληροφορίες ταυτότητας') }}</p>
    </div>
    <div class="form-section-body grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
        <div>
            <label class="label-plain">{{ __('Επώνυμο') }}</label>
            <input type="text" wire:model="lastname" class="input" placeholder="ΚΥΡΚΟΣ">
            @error('lastname') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="label-plain">{{ __('Όνομα') }} <span class="text-rose-500">*</span></label>
            <input type="text" wire:model="name" class="input" placeholder="ΚΩΝΣΤΑΝΤΙΝΟΣ">
            @error('name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="label-plain">{{ __('Email') }}</label>
            <input type="email" wire:model="email" class="input" placeholder="email@example.com">
            @error('email') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="label-plain">{{ __('URL slug') }}</label>
            <input type="text" wire:model="urlSlug" class="input" placeholder="kalpakidis">
            @error('urlSlug') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
            @if($urlSlug)
                <p class="help-text">
                    <span class="text-slate-400">URL:</span>
                    <a href="{{ url('/'.$urlSlug) }}" target="_blank" rel="noopener" class="text-brand-600 hover:underline">/{{ $urlSlug }}</a>
                </p>
            @else
                <p class="help-text">{{ __('Χρησιμοποιείται για το public URL του πιστοποιητικού.') }}</p>
            @endif
        </div>
        <div class="sm:col-span-2">
            <label class="label-plain">{{ __('Excel ID') }}</label>
            <input type="text" wire:model="externalId" class="input" placeholder="42">
            <p class="help-text">{{ __('Μοναδικό αναγνωριστικό για matching σε Excel imports.') }}</p>
            @error('externalId') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

<div class="form-section">
    <div class="form-section-head">
        <h2 class="section-title">{{ __('Κατηγορίες πιστοποιητικών') }}</h2>
        <p class="text-xs text-slate-500 mt-0.5">{{ __('Ποια πιστοποιητικά θα μπορεί να λάβει ο πελάτης') }}</p>
    </div>
    <div class="form-section-body">
        @if($categories->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($categories as $cat)
                    <label class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-md border cursor-pointer transition
                                  {{ in_array($cat->id, $selectedCategories) ? 'border-brand-500 bg-brand-50/40' : 'border-slate-200 hover:border-slate-300 bg-white' }}">
                        <span class="flex items-center gap-2.5 text-sm">
                            <input type="checkbox" value="{{ $cat->id }}" wire:model.live="selectedCategories" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            <span class="font-medium text-slate-700">{{ $cat->name }}</span>
                        </span>
                        @if($cat->html_template)
                            <span class="text-xs text-emerald-600">●</span>
                        @endif
                    </label>
                @endforeach
            </div>
        @else
            <p class="text-sm text-slate-500">{{ __('Δεν υπάρχουν κατηγορίες.') }}</p>
        @endif
    </div>
</div>

@if($customFields->count())
    @php
        $selectedCategoryObjects = $categories->whereIn('id', $selectedCategories);
    @endphp
    <div class="form-section">
        <div class="form-section-head">
            <h2 class="section-title">{{ __('Προσαρμοσμένα πεδία') }}</h2>
            <p class="text-xs text-slate-500 mt-0.5">
                {{ __('Στοιχεία που εμφανίζονται στο πιστοποιητικό. Συμπληρώνονται ανά κατηγορία.') }}
            </p>
        </div>
        <div class="form-section-body space-y-5">
            @forelse($selectedCategoryObjects as $cat)
                @php
                    $fieldsForCat = $customFields->filter(fn ($f) => $f->applies_to_all || $f->categories->contains('id', $cat->id));
                @endphp
                <div class="rounded-lg border border-slate-200 overflow-hidden">
                    <div class="px-4 py-2.5 bg-slate-50/60 border-b border-slate-200 flex items-center gap-2">
                        <i class="fas fa-folder text-slate-400 text-xs"></i>
                        <span class="text-sm font-medium text-slate-700">{{ $cat->name }}</span>
                    </div>
                    @if($fieldsForCat->isEmpty())
                        <div class="px-4 py-3 text-xs text-slate-500">
                            {{ __('Δεν υπάρχουν προσαρμοσμένα πεδία για αυτή την κατηγορία.') }}
                        </div>
                    @else
                        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                            @foreach($fieldsForCat as $field)
                                <div class="@if($field->type === 'checkbox') sm:col-span-2 @endif">
                                    <label class="label-plain">
                                        {{ $field->name }}
                                        @if($field->is_required) <span class="text-rose-500">*</span> @endif
                                    </label>
                                    @if($field->type === 'checkbox')
                                        <label class="inline-flex items-center gap-2 text-sm">
                                            <input type="checkbox" wire:model="customValues.{{ $cat->id }}.{{ $field->id }}" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                            <span class="text-slate-600">{{ __('Ναι') }}</span>
                                        </label>
                                    @else
                                        <input type="{{ $field->type === 'number' ? 'number' : ($field->type === 'date' ? 'date' : 'text') }}"
                                               wire:model="customValues.{{ $cat->id }}.{{ $field->id }}" class="input">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-500">
                    {{ __('Επίλεξε πρώτα κατηγορία πιο πάνω για να συμπληρώσεις τα πεδία του πιστοποιητικού.') }}
                </p>
            @endforelse
        </div>
    </div>
@endif
