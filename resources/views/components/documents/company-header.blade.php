@props([
    'title',
    'subtitle' => null,
])

@php($company = config('company'))

<header class="company-header">
  <div class="company-brand">
    <img src="{{ asset($company['logo']) }}" alt="Logo {{ $company['name'] }}" class="company-logo">
    <div class="company-info">
      <h1 class="company-name"><span class="company-name-tanjung">PT. TANJUNG</span> <span class="company-name-karya">KARYA</span> <span class="company-name-jaya">JAYA</span></h1>
      <p class="company-address">{{ $company['address'] }}</p>
      <p class="company-contact">Telp : {{ $company['phones'] }}</p>
      <p class="company-contact">Email : {{ $company['emails'] }}</p>
      <p class="company-contact">Website : {{ $company['website'] }}</p>
    </div>
  </div>
  <div>
    <h2 class="document-title">{{ $title }}</h2>
    @if ($subtitle)
      <p class="document-subtitle">{{ $subtitle }}</p>
    @endif
  </div>
</header>
