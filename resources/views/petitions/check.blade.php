<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checking Petition</title>
</head>
<body>
    <h1>Checking Petition</h1>
<p>
    You asked for petition ID: {{ $petitionId }}
</p>
<p>
    We found a petition with description: <em>{{ $petitionData->data->attributes->action }}</em>
</p>
<p>
    This petition is {{ $petitionData->data->attributes->state }}
</p>
<p>
    This petition has {{ $petitionData->data->attributes->signature_count }} signatures
</p>
</body>
</html>