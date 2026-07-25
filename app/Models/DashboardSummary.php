<?php

declare(strict_types=1);

namespace Orbit\Finance\Models;

final class DashboardSummary
{
    public function __construct(
        public readonly float $currentBalance,
        public readonly float $totalIncome,
        public readonly float $totalExpenses,
        public readonly float $monthlyVariation,
        public readonly float $goalProgress,
        public readonly int $transactionCount,
    ) {
    }

    public function toArray(): array
    {
        return [
            'current_balance' => $this->currentBalance,
            'total_income' => $this->totalIncome,
            'total_expenses' => $this->totalExpenses,
            'monthly_variation' => $this->monthlyVariation,
            'goal_progress' => $this->goalProgress,
            'transaction_count' => $this->transactionCount,
        ];
    }
}
