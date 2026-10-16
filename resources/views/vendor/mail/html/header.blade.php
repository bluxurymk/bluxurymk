@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
{!! $slot !!}
<span class="tagline">{{ __('site.tagline_secondary') }}</span>
</a>
</td>
</tr>
