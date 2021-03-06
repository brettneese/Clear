<?php
namespace CodeDay\Clear\Http\Controllers\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class SlackOauthController extends \CodeDay\Clear\Http\Controller {
  public function getOauth()
  {
    $code = \Input::get('code');
    $event = Models\Batch\Event::where("id", "=", \Input::get('state'))->firstOrFail();
    
    if (Models\User::me()->username != $event->manager_username
        && Models\User::me()->username != $event->evangelist_username
        && Models\User::me()->username != $event->coach_username
        && !$event->isUserAllowed(Models\User::me())
        && !Models\User::me()->is_admin) {
        \App::abort(401);
    }
    
    $oauth_data = Services\Slack::GetOauthAccess($code);

    if($oauth_data != null && $oauth_data != false && $event && $oauth_data->ok == true){
      $webhook = new Models\Application\Webhook;
      $webhook->url = $oauth_data->incoming_webhook->url;
      $webhook->event = "slack.registration.register.".$event->id;
      $webhook->application_id = \Config::get('slack.internal_app');
      $webhook->save();

      Services\Slack::SendPayloadToUrl([
        'text' => ":wave: Hello! If you see this, that means your Clear integration is working. Try registering someone through Clear!"
      ], $oauth_data->incoming_webhook->url);

      \Session::flash('status_message', 'Connected to Slack! We sent a test message, go look for it.');
      return \Redirect::to('/event/' . $event->id . '/slack');
    }else{
      \Session::flash('error', 'Could not authorize you with Slack. Please try again. (Slack error: '.$oauth_data->error.')');
      return \Redirect::to('/');
    }
  }
}
