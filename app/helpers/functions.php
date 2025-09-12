<?php

if (! function_exists('status_badge')) {
    function status_badge(string $status): string
    {
        switch ($status) {
            case 'approved':
                $classes = 'bg-green-500 text-white px-2 py-1 rounded text-xs';
                $label = 'approved';
                break;

            case 'rejected':
                $classes = 'bg-red-500 text-white px-2 py-1 rounded text-xs';
                $label = 'rejected';
                break;

            case 'pending':
            default:
                $classes = 'bg-yellow-500 text-white px-2 py-1 rounded text-xs';
                $label = 'pending';
                break;
        }

        return "<span class='{$classes}'>{$label}</span>";
    }
}

