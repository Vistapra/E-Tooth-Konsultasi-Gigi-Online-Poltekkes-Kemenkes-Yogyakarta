<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Container\Container;
use App\Models\ChMessage;

class WorkerCommand extends Command
{
    protected $signature = 'app:worker {action : The action to perform (work/status)}';
    protected $description = 'Run or check status of the queue worker';

    public function handle()
    {
        $action = $this->argument('action');
        $this->performAction($action);
    }

    public static function performAction($action)
    {
        switch ($action) {
            case 'work':
                self::work();
                break;
            case 'status':
                self::status();
                break;
            default:
                echo "Invalid action. Use 'work' or 'status'.\n";
        }
    }

    protected static function work()
    {
        echo "Processing jobs from the queue...\n";
        
        $job = DB::table('jobs')->orderBy('id')->first();
        
        if ($job) {
            try {
                $payload = json_decode($job->payload, true);
                if (!isset($payload['data']['command'])) {
                    throw new \Exception("Invalid job payload");
                }
                $command = unserialize($payload['data']['command']);
                
                if (!is_object($command)) {
                    throw new \Exception("Failed to unserialize job");
                }

                if (!method_exists($command, 'handle')) {
                    throw new \Exception("Job does not have a handle method");
                }

                // Get the method's parameters
                $reflector = new \ReflectionMethod(get_class($command), 'handle');
                $parameters = $reflector->getParameters();
                
                // Resolve dependencies
                $dependencies = [];
                foreach ($parameters as $parameter) {
                    if ($parameter->getClass()) {
                        $dependencies[] = Container::getInstance()->make($parameter->getClass()->name);
                    }
                }
                
                // Execute the job with resolved dependencies
                $command->handle(...$dependencies);
                
                // Remove the job from the queue
                DB::table('jobs')->where('id', $job->id)->delete();
                
                echo "Job processed successfully.\n";
            } catch (\Exception $e) {
                Log::error('Failed to process job: ' . $e->getMessage(), [
                    'job_id' => $job->id,
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                echo "Failed to process job: " . $e->getMessage() . "\n";
                
                // Increment attempts or remove the job if it has failed too many times
                $attempts = $job->attempts + 1;
                if ($attempts > 3) {
                    DB::table('jobs')->where('id', $job->id)->delete();
                    echo "Job removed after too many attempts.\n";
                } else {
                    DB::table('jobs')->where('id', $job->id)->update(['attempts' => $attempts]);
                    echo "Job attempts incremented.\n";
                }
            }
        } else {
            echo "No jobs in the queue.\n";
        }
    }

    protected static function status()
    {
        $count = DB::table('jobs')->count();
        echo "There are currently {$count} jobs in the queue.\n";
    }
}