<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacts</title>
</head>
<body>
    <h1>Contacts</h1>
    
    @if($contacts->count() > 0)
        <ul>
            @foreach($contacts as $contact)
                <li>
                    <strong>{{ $contact->name }}</strong><br>
                    Email: {{ $contact->email }}<br>
                    Phone: {{ $contact->phone }}
                </li>
            @endforeach
        </ul>
        
        {{ $contacts->links() }}
    @else
        <p>No contacts found.</p>
    @endif
</body>
</html> 