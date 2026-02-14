<?php

namespace App\Console\Commands;

use App\Models\Income;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Stock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ImportApiData extends Command
{
    protected $signature = 'api:import
        {entity : Entity to import (sales, orders, stocks, incomes, all)}
        {--dateFrom= : Start date (Y-m-d)}
        {--dateTo= : End date (Y-m-d)}
        {--limit=500 : Records per page}';

    protected $description = 'Import data from WB API into database';

    private string $apiHost;
    private string $apiKey;

    public function handle(): int
    {
        $this->apiHost = config('services.wb_api.host');
        $this->apiKey = config('services.wb_api.key');

        $entity = $this->argument('entity');
        $dateFrom = $this->option('dateFrom') ?? '2024-01-01';
        $dateTo = $this->option('dateTo') ?? now()->format('Y-m-d');
        $limit = (int) $this->option('limit');

        if ($entity === 'all') {
            $this->importSales($dateFrom, $dateTo, $limit);
            $this->importOrders($dateFrom, $dateTo, $limit);
            $this->importStocks($limit);
            $this->importIncomes($dateFrom, $dateTo, $limit);
        } else {
            match ($entity) {
                'sales' => $this->importSales($dateFrom, $dateTo, $limit),
                'orders' => $this->importOrders($dateFrom, $dateTo, $limit),
                'stocks' => $this->importStocks($limit),
                'incomes' => $this->importIncomes($dateFrom, $dateTo, $limit),
                default => $this->error("Unknown entity: {$entity}"),
            };
        }

        return Command::SUCCESS;
    }

    private function fetchPage(string $endpoint, array $params): ?array
    {
        $params['key'] = $this->apiKey;
        $url = $this->apiHost . $endpoint;

        try {
            $response = Http::timeout(60)->get($url, $params);
            if (!$response->successful()) {
                return null;
            }
            return $response->json();
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return null;
        }
    }

    private function importSales(string $dateFrom, string $dateTo, int $limit): void
    {
        $this->info('=== Importing Sales ===');
        Sale::truncate();

        $page = 1;
        $totalImported = 0;
        $lastPage = 1;

        do {
            $json = $this->fetchPage('/api/sales', [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'limit' => $limit,
                'page' => $page,
            ]);

            if (!$json || empty($json['data'])) break;

            $lastPage = $json['meta']['last_page'] ?? $page;
            $rows = [];

            foreach ($json['data'] as $item) {
                $rows[] = [
                    'g_number' => $item['g_number'] ?? null,
                    'date' => $item['date'] ?? null,
                    'last_change_date' => $item['last_change_date'] ?? null,
                    'supplier_article' => $item['supplier_article'] ?? null,
                    'tech_size' => $item['tech_size'] ?? null,
                    'barcode' => $item['barcode'] ?? null,
                    'total_price' => $item['total_price'] ?? null,
                    'discount_percent' => $item['discount_percent'] ?? null,
                    'is_supply' => $item['is_supply'] ?? null,
                    'is_realization' => $item['is_realization'] ?? null,
                    'promo_code_discount' => $item['promo_code_discount'] ?? null,
                    'warehouse_name' => $item['warehouse_name'] ?? null,
                    'country_name' => $item['country_name'] ?? null,
                    'oblast_okrug_name' => $item['oblast_okrug_name'] ?? null,
                    'region_name' => $item['region_name'] ?? null,
                    'income_id' => $item['income_id'] ?? null,
                    'sale_id' => $item['sale_id'] ?? null,
                    'odid' => $item['odid'] ?? null,
                    'spp' => $item['spp'] ?? null,
                    'for_pay' => $item['for_pay'] ?? null,
                    'finished_price' => $item['finished_price'] ?? null,
                    'price_with_disc' => $item['price_with_disc'] ?? null,
                    'nm_id' => $item['nm_id'] ?? null,
                    'subject' => $item['subject'] ?? null,
                    'category' => $item['category'] ?? null,
                    'brand' => $item['brand'] ?? null,
                    'is_storno' => $item['is_storno'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('sales')->insert($rows);
            $totalImported += count($rows);
            $this->output->write("\r  Page {$page}/{$lastPage} - Total: {$totalImported}");

            $page++;
            unset($json, $rows);
        } while ($page <= $lastPage);

        $this->newLine();
        $this->info("Imported {$totalImported} sales records.");
    }

    private function importOrders(string $dateFrom, string $dateTo, int $limit): void
    {
        $this->info('=== Importing Orders ===');
        Order::truncate();

        $page = 1;
        $totalImported = 0;
        $lastPage = 1;

        do {
            $json = $this->fetchPage('/api/orders', [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'limit' => $limit,
                'page' => $page,
            ]);

            if (!$json || empty($json['data'])) break;

            $lastPage = $json['meta']['last_page'] ?? $page;
            $rows = [];

            foreach ($json['data'] as $item) {
                $rows[] = [
                    'g_number' => $item['g_number'] ?? null,
                    'date' => $item['date'] ?? null,
                    'last_change_date' => $item['last_change_date'] ?? null,
                    'supplier_article' => $item['supplier_article'] ?? null,
                    'tech_size' => $item['tech_size'] ?? null,
                    'barcode' => $item['barcode'] ?? null,
                    'total_price' => $item['total_price'] ?? null,
                    'discount_percent' => $item['discount_percent'] ?? null,
                    'warehouse_name' => $item['warehouse_name'] ?? null,
                    'oblast' => $item['oblast'] ?? null,
                    'income_id' => $item['income_id'] ?? null,
                    'odid' => $item['odid'] ?? null,
                    'nm_id' => $item['nm_id'] ?? null,
                    'subject' => $item['subject'] ?? null,
                    'category' => $item['category'] ?? null,
                    'brand' => $item['brand'] ?? null,
                    'is_cancel' => $item['is_cancel'] ?? null,
                    'cancel_dt' => $item['cancel_dt'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('orders')->insert($rows);
            $totalImported += count($rows);
            $this->output->write("\r  Page {$page}/{$lastPage} - Total: {$totalImported}");

            $page++;
            unset($json, $rows);
        } while ($page <= $lastPage);

        $this->newLine();
        $this->info("Imported {$totalImported} orders records.");
    }

    private function importStocks(int $limit): void
    {
        $this->info('=== Importing Stocks ===');
        Stock::truncate();

        $page = 1;
        $totalImported = 0;
        $lastPage = 1;

        do {
            $json = $this->fetchPage('/api/stocks', [
                'dateFrom' => now()->format('Y-m-d'),
                'limit' => $limit,
                'page' => $page,
            ]);

            if (!$json || empty($json['data'])) break;

            $lastPage = $json['meta']['last_page'] ?? $page;
            $rows = [];

            foreach ($json['data'] as $item) {
                $rows[] = [
                    'date' => $item['date'] ?? null,
                    'last_change_date' => $item['last_change_date'] ?? null,
                    'supplier_article' => $item['supplier_article'] ?? null,
                    'tech_size' => $item['tech_size'] ?? null,
                    'barcode' => $item['barcode'] ?? null,
                    'quantity' => $item['quantity'] ?? null,
                    'is_supply' => $item['is_supply'] ?? null,
                    'is_realization' => $item['is_realization'] ?? null,
                    'quantity_full' => $item['quantity_full'] ?? null,
                    'warehouse_name' => $item['warehouse_name'] ?? null,
                    'in_way_to_client' => $item['in_way_to_client'] ?? null,
                    'in_way_from_client' => $item['in_way_from_client'] ?? null,
                    'nm_id' => $item['nm_id'] ?? null,
                    'subject' => $item['subject'] ?? null,
                    'category' => $item['category'] ?? null,
                    'brand' => $item['brand'] ?? null,
                    'sc_code' => $item['sc_code'] ?? null,
                    'price' => $item['price'] ?? null,
                    'discount' => $item['discount'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('stocks')->insert($rows);
            $totalImported += count($rows);
            $this->output->write("\r  Page {$page}/{$lastPage} - Total: {$totalImported}");

            $page++;
            unset($json, $rows);
        } while ($page <= $lastPage);

        $this->newLine();
        $this->info("Imported {$totalImported} stocks records.");
    }

    private function importIncomes(string $dateFrom, string $dateTo, int $limit): void
    {
        $this->info('=== Importing Incomes ===');
        Income::truncate();

        $page = 1;
        $totalImported = 0;
        $lastPage = 1;

        do {
            $json = $this->fetchPage('/api/incomes', [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'limit' => $limit,
                'page' => $page,
            ]);

            if (!$json || empty($json['data'])) break;

            $lastPage = $json['meta']['last_page'] ?? $page;
            $rows = [];

            foreach ($json['data'] as $item) {
                $rows[] = [
                    'income_id' => $item['income_id'] ?? null,
                    'number' => $item['number'] ?? null,
                    'date' => $item['date'] ?? null,
                    'last_change_date' => $item['last_change_date'] ?? null,
                    'supplier_article' => $item['supplier_article'] ?? null,
                    'tech_size' => $item['tech_size'] ?? null,
                    'barcode' => $item['barcode'] ?? null,
                    'quantity' => $item['quantity'] ?? null,
                    'total_price' => $item['total_price'] ?? null,
                    'date_close' => $item['date_close'] ?? null,
                    'warehouse_name' => $item['warehouse_name'] ?? null,
                    'nm_id' => $item['nm_id'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('incomes')->insert($rows);
            $totalImported += count($rows);
            $this->output->write("\r  Page {$page}/{$lastPage} - Total: {$totalImported}");

            $page++;
            unset($json, $rows);
        } while ($page <= $lastPage);

        $this->newLine();
        $this->info("Imported {$totalImported} incomes records.");
    }
}
