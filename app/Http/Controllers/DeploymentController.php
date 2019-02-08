<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeploymentController extends Controller
{
	public function deploy(Request $request)
	{
		$commands = ['cd /var/www/dance', 'git pull'];
		$signature = $request->header('X-Hub-Signature'); // $headers = getallheaders(); $headers['X-Hub-Signature']
		$payload = file_get_contents('php://input');
		if ($this->isFromGithub($payload, $signature)) {
			foreach ($commands as $command) {
				shell_exec($command);
			}
			http_response_code(200);
		} else {
			abort(403);
		}
	}
	/**
	 * @param $payload
	 * @param $signature
	 * @return bool
	 */
	private function isFromGithub($payload, $signature)
	{
		return 'sha1=' . hash_hmac('sha1', $payload, env('GITHUB_DEPLOY_TOKEN'), false) === $signature;
	}
}
