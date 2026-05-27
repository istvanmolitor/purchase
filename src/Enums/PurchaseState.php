<?php

namespace Molitor\Purchase\Enums;

enum PurchaseState: int
{
    case Planned = 0;
    case InProgress = 1;
    case Completed = 2;
    case Cancelled = 3;
}

