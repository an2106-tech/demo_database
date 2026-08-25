@php
    $selectedTemplate = $template ?? request('template', 'fpt-modern');
    $validTemplates = ['fpt-modern', 'ats-classic', 'tech-executive'];
    if (!in_array($selectedTemplate, $validTemplates, true)) {
        $selectedTemplate = 'fpt-modern';
    }
@endphp

@include('pdf.cv-templates.' . $selectedTemplate, [
    'candidate' => $candidate,
    'resume' => $resume,
])
