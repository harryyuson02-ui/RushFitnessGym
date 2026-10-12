@php
    $initialStep = $errors->hasAny(['fitness_goal']) ? 0
        : ($errors->hasAny(['first_name', 'last_name', 'gender', 'phone_country_code', 'phone', 'province', 'municipality', 'barangay', 'address']) ? 1
        : ($errors->hasAny(['availability_days', 'availability_starts_at', 'availability_ends_at']) ? 2
        : ($errors->any() ? 3 : 0)));
@endphp
<form method="POST" action="{{ route('member.application.store') }}" enctype="multipart/form-data" class="rush-card overflow-hidden" data-multistep-form data-initial-step="{{ $initialStep }}">
    @csrf
    <div class="grid grid-cols-4 border-b border-zinc-200 bg-zinc-50">
        @foreach(['Goal','Information','Availability','Membership'] as $index => $label)
            <div class="border-r border-zinc-200 px-2 py-4 text-center text-xs font-bold last:border-0" data-step-indicator="{{ $index }}">
                <span class="mx-auto mb-1 flex size-7 items-center justify-center rounded-full bg-zinc-200">{{ $index + 1 }}</span>
                <span class="hidden sm:inline">{{ $label }}</span>
            </div>
        @endforeach
    </div>

    <div class="p-5 sm:p-8">
        <x-input-error :messages="$errors->all()" />

        <section data-step-panel="0">
            <h3 class="text-xl font-bold">What is your main fitness goal?</h3>
            <p class="mt-1 text-sm text-zinc-500">Your coach will use this to personalize your training.</p>
            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach(['Weight loss','Build muscle','Improve strength','Improve endurance','General fitness','Mobility and flexibility'] as $goal)
                    <label class="rush-choice-card"><input type="radio" name="fitness_goal" value="{{ $goal }}" @checked(old('fitness_goal') === $goal) required><span class="font-semibold">{{ $goal }}</span></label>
                @endforeach
            </div>
        </section>

        <section data-step-panel="1" hidden>
            <h3 class="text-xl font-bold">Client information</h3>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <label class="rush-label">First name<input class="rush-input" name="first_name" value="{{ old('first_name', str(auth()->user()->name)->before(' ')) }}" required></label>
                <label class="rush-label">Last name<input class="rush-input" name="last_name" value="{{ old('last_name', str(auth()->user()->name)->after(' ')) }}" required></label>
                <label class="rush-label">Gender<select class="rush-input" name="gender" required><option value="">Select gender</option>@foreach(['Female','Male','Non-binary','Prefer not to say'] as $option)<option @selected(old('gender') === $option)>{{ $option }}</option>@endforeach</select></label>
                <label class="rush-label">Mobile number
                    <span class="grid grid-cols-[minmax(0,9rem)_minmax(0,1fr)] gap-2">
                        <select class="rush-input" name="phone_country_code" aria-label="Country calling code" required>
                            <option value="+63">Philippines +63</option>
                        </select>
                        <input class="rush-input" type="tel" name="phone" value="{{ old('phone') }}" inputmode="numeric" autocomplete="tel-national" pattern="09[0-9]{9}" placeholder="09171234567" title="Enter a Philippine mobile number with exactly 11 digits starting with 09 (e.g. 09171234567)." aria-describedby="member-phone-help member-phone-error" data-ph-mobile required>
                    </span>
                    <span id="member-phone-help" class="text-xs font-normal text-zinc-500">Use 09XXXXXXXXX: exactly 11 digits, without spaces, letters, or symbols.</span>
                    <span id="member-phone-error" class="text-xs font-normal text-red-600" aria-live="polite" hidden></span>
                    <x-input-error :messages="$errors->get('phone')" />
                </label>
                <x-location-selects />
            </div>
        </section>

        <section data-step-panel="2" hidden>
            <h3 class="text-xl font-bold">Schedule availability</h3>
            <p class="mt-1 text-sm text-zinc-500">Choose every day you are usually available and your preferred time range.</p>
            <fieldset class="mt-5"><legend class="text-sm font-bold">Available days</legend><div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">@foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)<label class="rush-choice justify-center"><input type="checkbox" name="availability_days[]" value="{{ $day }}" @checked(in_array($day, old('availability_days', []), true))> {{ $day }}</label>@endforeach</div></fieldset>
            <div class="mt-6 grid gap-4 sm:grid-cols-2"><label class="rush-label">Available from<input class="rush-input" type="time" name="availability_starts_at" value="{{ old('availability_starts_at') }}" required></label><label class="rush-label">Available until<input class="rush-input" type="time" name="availability_ends_at" value="{{ old('availability_ends_at') }}" required></label></div>
        </section>

        <section data-step-panel="3" data-membership-pricing hidden>
            <div><p class="text-[0.65rem] font-bold uppercase tracking-[0.18em] text-lime-700">Subscription catalog</p><h3 class="mt-2 text-xl font-bold">Select a membership plan</h3><p class="mt-1 text-sm text-zinc-500">Simple plans. Clear pricing. Plan management is available only to administrators.</p></div>
            <div class="mt-5 grid gap-5 lg:grid-cols-3">
                @forelse($plans as $plan)
                    @php($planImage = match (true) { $plan->duration_days >= 365 => 'annual.jpg', $plan->duration_days >= 90 => 'quarterly.jpg', default => 'monthly.jpg' })
                    <label class="group cursor-pointer overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md has-[:checked]:border-lime-500 has-[:checked]:ring-2 has-[:checked]:ring-lime-400">
                        <img src="{{ asset('images/plans/'.$planImage) }}" alt="" class="h-36 w-full object-cover">
                        <span class="block p-5"><span class="flex items-start gap-3"><input class="mt-1 size-4 accent-lime-500" type="radio" name="membership_plan_id" value="{{ $plan->id }}" data-plan-price="{{ $plan->price }}" @checked((string) old('membership_plan_id') === (string) $plan->id) required><span class="min-w-0 flex-1"><span class="flex justify-between gap-3"><strong>{{ $plan->name }}</strong><strong>₱{{ number_format($plan->price, 2) }}</strong></span><span class="mt-2 block text-sm text-zinc-500">{{ $plan->duration_days }} days</span><span class="mt-2 block text-sm leading-5 text-zinc-600">{{ $plan->description }}</span></span></span></span>
                    </label>
                @empty
                    <p class="rounded-xl bg-amber-50 p-4 text-sm text-amber-900 lg:col-span-3">No active subscription plans are available yet.</p>
                @endforelse
            </div>

            <h3 class="mt-8 text-xl font-bold">Choose your coaching preference</h3>
            <p class="mt-1 text-sm text-zinc-500">Select a coach for guided training or choose independent gym access. Coaching fees are negotiated directly with your chosen coach and are not included in the gym membership payment.</p>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <label class="rush-choice-card sm:col-span-2"><input type="radio" name="coach_id" value="" @checked(old('coach_id', '') === '') required><span><strong class="block">No coach — gym access only</strong><span class="text-sm text-lime-800">Independent training</span><span class="mt-1 block text-xs text-zinc-500">Use the gym and lift weights without coaching support.</span></span></label>
                @forelse($coaches as $coach)
                    <label class="rush-choice-card"><input type="radio" name="coach_id" value="{{ $coach->id }}" data-coach-option @checked((string) old('coach_id') === (string) $coach->id) required><span><strong class="block">{{ $coach->name }}</strong><span class="text-sm text-lime-800">{{ $coach->specialty }}</span><span class="mt-1 block text-xs text-zinc-500">{{ $coach->schedule }} · Coaching terms arranged separately</span></span></label>
                @empty
                    <p class="text-sm text-amber-800 sm:col-span-2">No coaches are accepting clients right now. Independent gym access is still available.</p>
                @endforelse
            </div>

            <div class="mt-6 rounded-xl border border-lime-200 bg-lime-50 p-5" data-membership-total>
                <div class="flex items-center justify-between gap-4"><span class="font-semibold text-lime-950">Amount to pay</span><strong class="text-2xl text-lime-950" data-membership-total-amount>Choose a plan</strong></div>
                <p class="mt-1 text-xs text-lime-800" data-membership-total-breakdown>The total updates when you select a plan and coaching preference.</p>
            </div>

            <fieldset class="mt-8"><legend class="text-xl font-bold">Payment method</legend><div class="mt-3 flex flex-wrap gap-3"><label class="rush-choice"><input type="radio" name="payment_method" value="gcash" @checked(old('payment_method', 'gcash') === 'gcash')> GCash</label><label class="rush-choice"><input type="radio" name="payment_method" value="cash" @checked(old('payment_method') === 'cash')> Cash</label></div></fieldset>
            <div class="mt-5 grid gap-4 sm:grid-cols-2" data-gcash-fields><label class="rush-label">GCash reference number<input class="rush-input" name="reference" value="{{ old('reference') }}" inputmode="numeric" minlength="13" maxlength="13" pattern="[0-9]{13}" placeholder="13-digit reference" title="Enter exactly 13 digits"></label><label class="rush-label">Upload GCash receipt<input class="rush-input py-3" type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf"></label><p class="text-sm leading-6 text-zinc-500 sm:col-span-2">Enter the exact 13-digit reference shown in GCash. An administrator will compare it with the receipt and total amount before approval.</p></div>
            <div class="mt-6 rounded-xl bg-lime-50 p-5 text-sm leading-6 text-lime-950"><p class="font-bold">Application review</p><p class="mt-1">The administrator verifies your payment and membership first. If you selected a coach, the coach receives a separate training request after membership approval.</p></div>
        </section>

        <div class="mt-7 flex justify-between gap-3"><button type="button" class="rush-secondary" data-step-previous hidden>Back</button><span></span><button type="button" class="rush-primary" data-step-next>Continue</button><button type="submit" class="rush-primary" data-step-submit hidden>Submit application</button></div>
    </div>
</form>
