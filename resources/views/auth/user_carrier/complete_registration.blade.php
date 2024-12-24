<x-guest-layout>
    <h1>Complete Your Carrier Registration</h1>

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('user_carrier.complete_registration') }}">
        @csrf

        <div>
            <label for="carrier_name">Carrier Name</label>
            <input type="text" name="carrier_name" id="carrier_name" value="{{ old('carrier_name') }}" required>
        </div>

        <div>
            <label for="carrier_address">Address</label>
            <input type="text" name="carrier_address" id="carrier_address" value="{{ old('carrier_address') }}" required>
        </div>

        <div>
            <label for="state">State</label>
            <input type="text" name="state" id="state" value="{{ old('state') }}" required>
        </div>

        <div>
            <label for="zipcode">Zip Code</label>
            <input type="text" name="zipcode" id="zipcode" value="{{ old('zipcode') }}" required>
        </div>

        <div>
            <label for="ein_number">EIN Number</label>
            <input type="text" name="ein_number" id="ein_number" value="{{ old('ein_number') }}" required>
        </div>

        <button type="submit">Complete Registration</button>
    </form>
</x-guest-layout>
