import '../plugins/dynamicListener.js';

/**
 * This class handles donor wall shortcode related features
 *
 * @since 2.2.0
 *
 */
class GiveDonorWall {
	constructor() {
		window.addEventListener(
			'load', function () {
				let readMoreLinks = document.querySelectorAll('.give-donor__read-more'),
					loadMoreBtn = document.querySelectorAll('.give-donor__load_more');

				/**
				 * Add events
				 */
				window.addDynamicEventListener(document, 'click', '.give-donor__read-more', GiveDonorWall.readMoreBtnEvent);
				window.addDynamicEventListener(document, 'click', '.give-donor__load_more', GiveDonorWall.loadMoreBtnEvent);

				// if( readMoreLinks.length ) {
				// 	Array.from( readMoreLinks ).forEach(
				// 		function (el) {
				// 			GiveDonorWall.readMoreBtnEvent( el );
				// 		}
				// 	);
				// }
				//
				// if( loadMoreBtn.length ) {
				// 	Array.from( loadMoreBtn ).forEach(
				// 		function (el) {
				// 			GiveDonorWall.loadMoreBtnEvent( el );
				// 		}
				// 	);
				// }
			}, false
		);
	}

	/**
	 * Add click event to read more link
	 *
	 * @since  2.2.0
	 *
	 * @param {object} evt
	 */
	static readMoreBtnEvent(evt) {
		evt.preventDefault();

		jQuery.magnificPopup.open(
			{
				items: {
					src: evt.target.parentNode.parentNode.parentNode.parentNode.getElementsByClassName('give-donor__comment')[0].innerHTML,
					type: 'inline',
				},
				mainClass: 'give-modal give-donor-wall-modal',
				closeOnBgClick: false,
			}
		);

		return false;
	}

	/**
	 * Add click event to load more link
	 *
	 * @since  2.2.0
	 *
	 * @param {object} evt
	 */
	static loadMoreBtnEvent(evt) {
		evt.preventDefault();

		jQuery.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'give_get_donor_comments',
				data: evt.target.getAttribute('data-shortcode')
			}
		}).done(function (res) {
			if (res.html.length) {
				evt.target.parentNode.getElementsByClassName('give-grid')[0].insertAdjacentHTML('beforeend', res.html);
			}

			if (!res.remaining) {
				evt.target.remove()
			}
		});

		return false;
	}
}

let giveDonorWall = new GiveDonorWall();
