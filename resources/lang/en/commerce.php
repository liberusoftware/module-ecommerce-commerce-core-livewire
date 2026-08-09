<?php

return [

    'loading' => 'Working…',

    'store' => [
        'heading' => 'Stores',
        'name' => 'Store name',
        'create' => 'Create store',
        'saved' => 'Store saved.',
        'empty' => 'No stores yet.',
        'slug' => 'Slug',
        'currency' => 'Currency',
        'locale' => 'Locale',
        'timezone' => 'Timezone',
        'open' => 'Open',
    ],

    'channel' => [
        'heading' => 'Channels',
        'name' => 'Channel name',
        'theme' => 'Theme',
        'create' => 'Create channel',
        'empty' => 'This store has no channels yet.',
        'primary_host' => 'Primary hostname',
        'no_primary_host' => 'No hostname yet',
        'select' => 'Select',
        'selected' => 'Selected channel',
        'none_selected' => 'Select a channel to manage its status and hostnames.',
    ],

    'domain' => [
        'heading' => 'Hostnames',
        'host' => 'Hostname',
        'add' => 'Add hostname',
        'primary' => 'Primary',
        'make_primary' => 'Make primary',
        'remove' => 'Remove',
        'empty' => 'This channel answers on no hostname yet.',
        'claimed' => 'The hostname :host is already claimed by another channel.',
    ],

    'setting' => [
        'heading' => 'Settings',
        'key' => 'Key',
        'value' => 'Value',
        'save' => 'Save setting',
        'forget' => 'Forget',
        'empty' => 'This store has no settings yet.',
    ],

    'capability' => [
        'heading' => 'Capabilities',
        'enable' => 'Enable',
        'disable' => 'Disable',
        'on' => 'Enabled',
        'off' => 'Disabled',
        'unknown' => 'That capability is not one this release knows about.',
    ],

    'order_number' => [
        'heading' => 'Order numbering',
        'prefix' => 'Prefix',
        'allocate' => 'Allocate a number',
        'allocated' => 'Allocated :number.',
    ],

    'context' => [
        'heading' => 'Commercial context',
        'unresolved' => 'No storefront resolved for this request.',
        'store' => 'Store',
        'channel' => 'Channel',
        'team' => 'Team',
        'currency' => 'Currency',
        'locale' => 'Locale',
        'timezone' => 'Timezone',
    ],

    'status' => [
        'heading' => 'Status',
        'current' => 'Currently :status.',
        'move_to' => 'Move to :status',
        'terminal' => 'This status is final; there is nowhere left to move.',
        'unknown' => 'That status is not one this release knows about.',
        'illegal' => 'A move from :from to :to is not allowed.',
    ],

];
