<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Driver Form</title>
    @livewireStyles
</head>
<body>
    <div class="container mx-auto p-4">
        <h1>Test Driver Application Form</h1>
        
        @livewire('driver.steps.application-step')
    </div>
    
    @livewireScripts
</body>
</html>