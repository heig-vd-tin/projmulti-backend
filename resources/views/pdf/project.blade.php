<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $project->title }}</title>
    <style>
        @page { margin: 18mm 16mm 20mm 16mm; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 10.5pt; color: #1d2433; line-height: 1.45; }
        header { border-bottom: 2px solid #c8102e; padding-bottom: 8px; margin-bottom: 14px; }
        .school { font-size: 8.5pt; color: #6b7280; letter-spacing: .04em; text-transform: uppercase; }
        h1 { font-size: 17pt; margin: 4px 0 6px; }
        .meta { font-size: 9pt; color: #4b5563; }
        .chip { display: inline-block; border: 1px solid #c7ccd6; border-radius: 9px; padding: 1px 7px; margin-right: 4px; font-size: 8.5pt; }
        .summary { background: #f4f5f8; border-left: 3px solid #c8102e; padding: 8px 10px; margin: 12px 0 16px; font-style: italic; }
        .description img { max-width: 100%; height: auto; }
        .description h1, .description h2, .description h3 { font-size: 12.5pt; margin: 12px 0 4px; }
        .description p { margin: 0 0 6px; }
        .ql-align-center { text-align: center; }
        .ql-align-right { text-align: right; }
        .ql-align-justify { text-align: justify; }
        .ql-indent-1 { padding-left: 2em; }
        .ql-indent-2 { padding-left: 4em; }
        footer { position: fixed; bottom: -12mm; left: 0; right: 0; font-size: 8pt; color: #9ca3af; text-align: right; }
    </style>
</head>
<body>
<footer>Projet multidisciplinaire HEIG-VD · cours {{ $project->year }} · exporté le {{ now()->format('d.m.Y') }}</footer>
<header>
    <div class="school">HEIG-VD · Projet multidisciplinaire {{ $project->year }}</div>
    <h1>{{ $project->title }}</h1>
    <div class="meta">
        Responsable : {{ $project->owner?->firstname }} {{ $project->owner?->lastname }}
        @if($project->owner?->email) · {{ $project->owner->email }} @endif
    </div>
    @if($project->domains->isNotEmpty())
        <div class="meta" style="margin-top:6px">
            Domaines :
            @foreach($project->domains as $domain)<span class="chip">{{ $domain->name }}</span>@endforeach
        </div>
    @endif
</header>

<div class="summary">{{ $project->short_description }}</div>

<div class="description">{!! $project->description !!}</div>
</body>
</html>
