<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Customers\FindLostCustomers;
use App\Jobs\NotifyLostCustomerJob;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

#[Signature('crm:detect-lost-customers {--days= : Override the inactivity threshold in days}')]
#[Description('Detect lost customers and dispatch re-engagement notifications')]
final class DetectLostCustomersCommand extends Command
{
    public function handle(FindLostCustomers $findLostCustomers): int
    {
        $days = (int) ($this->option('days') ?? config('crm.lost_customer_days', 90));

        $this->info(sprintf('Looking for customers with no purchase in the last %d day(s)…', $days));

        $customers = $findLostCustomers->execute($days);

        if ($customers->isEmpty()) {
            $this->info('No lost customers found. Great retention!');

            return CommandAlias::SUCCESS;
        }

        $this->info(sprintf('Found %d lost customer(s).', $customers->count()));

        $bar = $this->output->createProgressBar($customers->count());
        $bar->start();

        foreach ($customers as $customer) {
            NotifyLostCustomerJob::dispatch($customer);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Re-engagement jobs dispatched successfully.');

        return CommandAlias::SUCCESS;
    }
}
