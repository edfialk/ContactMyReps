<template>
	<div class="row">
		<div class="container">
			<div class="row" itemscope itemtype="http://schema.org/Person">
				<div class="col-sm-6">
					<div class="row">
						<div class="col-xs-offset-1 col-xs-5 col-sm-offset-0 img-wrap">
							<img v-if="item.photo" v-bind:src="item.photo" itemprop="image" alt="{{ item.name }}">
						</div>
						<div class="col-xs-6 col-sm-7">
							<p class="name" itemprop="name">
								<a v-if="website" href="{{ website }}">{{ item.name }} {{ party }}</a>
								<span v-else>{{ item.name}} {{ party }}</span>
							</p>
							<p class="office" itemprop="jobTitle">{{ item.office }}</p>
							<p v-if="phone" itemprop="telephone"><strong>Telephone:</strong> {{ phone }}</p>
							<address v-if="address" itemprop="address"><strong>Address:</strong><br>{{{ address }}}</address>
						</div>
					</div>
				</div>
				<div class="col-xs-offset-1 col-sm-5 item-links">
					<p v-if="website"><a href="{{ website }}"><i class="fa fa-fw fa-desktop"></i> Homepage</a></p>
					<p v-if="item.contact_form"><a href="{{ item.contact_form }}"><i class="fa fa-fw fa-envelope"></i> Email</a></p>
					<p v-if="email"><a href="mailto:{{ email }}"><i class="fa fa-fw fa-envelope"></i> Email</a></p>
					<p v-if="item.facebook_id"><a href="http://facebook.com/{{ item.facebook_id }}"><i class="fa fa-fw fa-facebook-official"></i> Facebook.com/{{ item.facebook_id }}</a></p>
					<p v-if="item.twitter_id"><a href="http://twitter.com/{{ item.twitter_id }}"><i class="fa fa-fw fa-twitter"></i> @{{ item.twitter_id }}</a></p>
					<p v-if="item.google_id"><a href="http://plus.google.com/{{ item.google_id }}"><i class="fa fa-fw fa-google-plus"></i> {{ item.google_id }}</a></p>
					<p v-if="item.youtube_id"><a href="http://youtube.com/{{ item.youtube_id }}"><i class="fa fa-fw fa-youtube"></i> {{ item.youtube_id }}</a></p>
					<p v-if="role"><a href="/edit/{{ item._id }}?redirect={{ redirect }}"><i class="fa fa-fw fa-flag"></i> Edit</a></p>
					<p v-else><a v-on:click="flag" href="#"><i class="fa fa-fw fa-flag"></i> Report Broken Link</a></p>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import request from 'superagent';

	export default {
		name: 'Item',
		props: {
			item: Object,
			role: Boolean
		},
		computed: {
			district() {
				return this.item.district ? 'District ' + this.item.district : '';
			},
			party() {
				return this.item.party ? '[' + this.item.party[0] + ']' : '';
			},
			address() {
				let a = this.item.address;
				if (typeof a == "string")
					return a;
				if (Array.isArray(a))
					return a.join('<br>');
				return '';
			},
			phone() {
				if (typeof this.item.phone == 'undefined' && typeof this.item.phones == 'undefined')
					return '';
				var phone = '';
				if (typeof this.item.phone == "string")
					phone = this.item.phone;
				else if (Array.isArray(this.item.phones) && this.item.phones.length > 0)
					phone = this.item.phones[0];
				phone = phone.replace('(', '').replace(') ', '-');
				return phone;
			},
			email() {
				if (!this.item.email && !this.item.emails)
					return '';
				if (typeof this.item.email == "string")
					return this.item.email;
				else if (Array.isArray(this.item.emails))
					return this.item.emails[0];
				return '';
			},
			website() {
				if (!this.item.website && !this.item.urls)
					return '';
				if (typeof this.item.website == "string")
					return this.item.website;
				else if (Array.isArray(this.item.urls))
					return this.item.urls[0];
				return '';
			},
			redirect() {
				return window.location.pathname;
			}
		},
		methods: {
			flag(e) {
				e.preventDefault();
		    	request('/rep/' + this.item._id + '/flag', (err, res) => {});
		    	let $a = $('<p>Thanks!</p>');
				$(e.target).replaceWith($a);
				window.setTimeout(function(){
					$a.fadeOut();
				}, 2000);

			}
		}
	};
</script>

<style lang="sass">
	.results img {
		margin: 0 auto;
		width: 180px;
		max-width: 100%;
		@media screen and (max-width: 767px){
			margin-bottom: 10px;
		}
	}
	.results .row {
		padding-top: 10px;
		padding-bottom: 10px;
	}
	.results .row:nth-of-type(even) {
		background: #DEE1EA;
	}
	.name {
		font-size: 20px;
		font-weight: bold;
		margin-bottom: 5px;
	}
	.office {
		font-size: 18px;
	}
	.item-links {
		padding-top: 10px;
		@media screen and (max-width: 767px){
			padding-top: 0;
		}
	}
	address {
		margin-bottom: 10px;
	}
</style>