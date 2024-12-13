<x-guest-layout>
<form method="POST" action="{{ route('user_driver.register') }}">
    @csrf
    <div>
        <label for="first_name">First Name</label>
        <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required>
    </div>

    <div>
        <label for="last_name">Last Name</label>
        <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required>
    </div>

    <div>
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required>
    </div>

    <div>
        <label for="license_number">License Number</label>
        <input type="text" name="license_number" id="license_number" value="{{ old('license_number') }}" required>
    </div>

    <div>
        <label for="birth_date">Birth Date</label>
        <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date') }}" required>
    </div>

    <div>
        <label for="phone">Phone</label>
        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required>
    </div>

    <div>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
    </div>

    <div>
        <label for="password_confirmation">Confirm Password</label>
        <input type="password" name="password_confirmation" id="password_confirmation" required>
    </div>

    <button type="submit">Register</button>
</form>
</x-guest-layout>