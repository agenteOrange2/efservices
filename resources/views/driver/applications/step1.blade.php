<x-guest-layout>
    {{-- resources/views/driver/steps/step1.blade.php --}}
<div class="progress-bar">
    <div class="w-1/4 bg-blue-500 h-2"></div>
  </div>
  
  <form x-data="{
      name: '{{ $driver->name }}',
      middleName: '{{ $driver->middle_name }}',
      lastName: '{{ $driver->last_name }}',
      phone: '{{ $driver->phone }}',
      email: '{{ $driver->email }}',
      dateOfBirth: '{{ $driver->date_of_birth }}',
      ssn: ''
  }" method="POST">
    @csrf
    
    {{-- Reuse your existing form fields --}}
    <div class="grid grid-cols-2 gap-4">
      <x-input label="Name" x-model="name" name="name" required />
      <x-input label="Middle Name" x-model="middleName" name="middle_name" />
      {{-- Add other fields --}}
    </div>
  
    <button type="submit">Next Step</button>
  </form>
</x-guest-layout>