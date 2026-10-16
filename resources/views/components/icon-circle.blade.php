@props(['name', 'size' => 'md'])

<span {{ $attributes->class([
    'inline-flex shrink-0 items-center justify-center rounded-full border border-gold-500/70 text-gold-400 shadow-[inset_0_0_12px_rgb(201_161_91/0.15)]',
    'size-10' => $size === 'sm',
    'size-14' => $size === 'md',
    'size-20' => $size === 'lg',
]) }}>
    <x-icon :name="$name" @class(['size-5' => $size === 'sm', 'size-7' => $size === 'md', 'size-9' => $size === 'lg']) />
</span>
