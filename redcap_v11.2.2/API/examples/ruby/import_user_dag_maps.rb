#!/usr/bin/env ruby

require 'json'
require 'curl'
require './settings.rb'
include Settings

record = {
  :username => 'testuser',
  :redcap_data_access_group => 'api_testing_group'
}

data = [record].to_json

fields = {
  :token    => Settings::API_TOKEN,
  :content  => 'userDagMapping',
  :action   => 'import',
  :format   => 'json',
  :data     => data,
}

ch = Curl::Easy.http_post(
  Settings::API_URL,
  fields.collect{|k, v| Curl::PostField.content(k.to_s, v)}
)
puts ch.body_str
