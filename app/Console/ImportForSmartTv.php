<?php

namespace App\Console\Commands;

use App\Episode;
use App\Library\Services\Cache\ChannelsCaching;
use App\Library\Services\Import\SmartTvImporter;
use Illuminate\Console\Command;

class ImportForSmartTv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:smarttv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data for SmartTV';

    /**
     * @var SmartTvImporter
     */
    private $importer;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SmartTvImporter $importer)
    {
        parent::__construct();
        $this->importer = $importer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("Starting update.");

        $this->info("Getting categories for Smart TV.");
        $categories = $this->importer->getCategories();
        $this->info("Got " . count($categories) . " categories for Smart TV. Saving...");
        $this->importer->saveCategories($categories);

        $this->info("Getting channels.");
        $channels = $this->importer->getChannels();
        $this->info("Got " . count($channels) . " channels. Saving...");
        $this->importer->saveChannels($channels);

        if (count($categories) and count($channels)) {
            $this->info("Getting broadcasts.");
            $broadcasts = $this->importer->getBroadcasts();
            $this->info("Got " . count($broadcasts) . " broadcasts. Saving...");
            $this->importer->saveBroadcasts($broadcasts, $channels[0]['id_in_api']);
        } else {
            $this->error("No found category and channel for adding broadcasts.");
        }

        $this->info("Setting channels with broadcasts to cache.");
        ChannelsCaching::warmup();

        $this->info("Getting archives.");
        $archives = $this->importer->getArchive();
        $this->info("Has " . count($archives) . " episodes in archive for Smart TV.");
        $this->importer->saveArchive($archives);
        $this->info("Got " . Episode::count() . " channels. Saving...");

        $this->info("Done.");
    }
}
