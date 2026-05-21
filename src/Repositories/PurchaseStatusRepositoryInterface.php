<?php

namespace Molitor\Purchase\Repositories;

interface PurchaseStatusRepositoryInterface
{
	/**
	 * @return array<int, string>
	 */
	public function getOptions(): array;
}
