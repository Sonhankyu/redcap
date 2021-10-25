<?php
namespace Aws\MediaTailor;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS MediaTailor** service.
 * @method \Aws\Result createChannel(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createChannelAsync(array $args = [])
 * @method \Aws\Result createProgram(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createProgramAsync(array $args = [])
 * @method \Aws\Result createSourceLocation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createSourceLocationAsync(array $args = [])
 * @method \Aws\Result createVodSource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createVodSourceAsync(array $args = [])
 * @method \Aws\Result deleteChannel(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteChannelAsync(array $args = [])
 * @method \Aws\Result deleteChannelPolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteChannelPolicyAsync(array $args = [])
 * @method \Aws\Result deletePlaybackConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deletePlaybackConfigurationAsync(array $args = [])
 * @method \Aws\Result deleteProgram(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteProgramAsync(array $args = [])
 * @method \Aws\Result deleteSourceLocation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteSourceLocationAsync(array $args = [])
 * @method \Aws\Result deleteVodSource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteVodSourceAsync(array $args = [])
 * @method \Aws\Result describeChannel(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeChannelAsync(array $args = [])
 * @method \Aws\Result describeProgram(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeProgramAsync(array $args = [])
 * @method \Aws\Result describeSourceLocation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeSourceLocationAsync(array $args = [])
 * @method \Aws\Result describeVodSource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeVodSourceAsync(array $args = [])
 * @method \Aws\Result getChannelPolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getChannelPolicyAsync(array $args = [])
 * @method \Aws\Result getChannelSchedule(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getChannelScheduleAsync(array $args = [])
 * @method \Aws\Result getPlaybackConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getPlaybackConfigurationAsync(array $args = [])
 * @method \Aws\Result listAlerts(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listAlertsAsync(array $args = [])
 * @method \Aws\Result listChannels(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listChannelsAsync(array $args = [])
 * @method \Aws\Result listPlaybackConfigurations(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listPlaybackConfigurationsAsync(array $args = [])
 * @method \Aws\Result listSourceLocations(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listSourceLocationsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result listVodSources(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listVodSourcesAsync(array $args = [])
 * @method \Aws\Result putChannelPolicy(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putChannelPolicyAsync(array $args = [])
 * @method \Aws\Result putPlaybackConfiguration(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putPlaybackConfigurationAsync(array $args = [])
 * @method \Aws\Result startChannel(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startChannelAsync(array $args = [])
 * @method \Aws\Result stopChannel(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopChannelAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateChannel(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateChannelAsync(array $args = [])
 * @method \Aws\Result updateSourceLocation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateSourceLocationAsync(array $args = [])
 * @method \Aws\Result updateVodSource(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateVodSourceAsync(array $args = [])
 */
class MediaTailorClient extends AwsClient {}
