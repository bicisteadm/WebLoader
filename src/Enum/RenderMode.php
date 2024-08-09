<?php
declare(strict_types=1);

namespace WebLoader\Enum;

enum RenderMode: string
{
	case URL = 'url';
	case LINK = 'link';
	case INLINE = 'inline';
}
