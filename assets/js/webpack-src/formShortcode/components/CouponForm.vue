<template>
  <div class="wpcd-fs-w-full-important">
    <button @click.prevent="$emit('switch', 'UserCoupons')" class="wpcd-fs-pointer">
      {{ extras.strings.back_to_coupons | cap }}
    </button>
    <div class="wpcd-fs-flex wpcd-fs-flex-col">
      <form id="form-shortcode-form-wrapper" @submit.prevent="" method="post">
        <table class="wpcd-fs-table">
          <coupon-type v-model="store['coupon-type']" :typedata="couponType" />
          <coupon-title helpmessage="enter coupon title" id="coupon-title" label="Coupon Title" />
          <tr is="CouponTypeForm" :fieldsdata="parsedFields" />
        </table>
      </form>
      <terms-component :taxonomies="extras.terms" />
      <featured-image :featured-url="store.featured_url" />
      <submit-component :message="submitMessage" @submit="submitForm" />
    </div>
    <coupon-preview />
  </div>
</template>
<script>
import CouponType from './CouponType';
import CouponTypeForm from './CouponTypeForm';
import CouponPreview from './CouponPreview';
import CouponTitle from './CouponTitle';
import SubmitComponent from './SubmitComponent';
import TermsComponent from './TermsComponent';
import FeaturedImage from './FeaturedImage';
import logo from '../assets/image/icon-128x128.png';

export default {
  props: ['fields'],
  components: {
    FeaturedImage,
    TermsComponent,
    CouponTitle,
    CouponType,
    CouponTypeForm,
    CouponPreview,
    SubmitComponent,
  },
  data() {
    return {
      showSampleField: false,
      couponType: this.fields.filter(f => f.id === 'coupon-type')[0],
      submitMessage: '',
      logo,
    };
  },
  methods: {
    submitForm() {
      this.app.submit.fetching = true;
      try {
        const formData = new FormData();
        const data = { ...this.store, ...{ action: this.extras.form_action, nonce: this.extras.nonce } };

        Object.keys(data).map(k => {
          if (Object.prototype.hasOwnProperty.call(data, k)) {
            formData.set(k, data[k]);
          }
        });

        // inject new terms to FormData
        const { newTerms } = this.app;
        if (newTerms) {
          formData.set('new_terms', JSON.stringify(newTerms));
        }

        // inject terms object to FormData
        if (this.store.terms) {
          Object.keys(this.store.terms).map(k => {
            if (Object.prototype.hasOwnProperty.call(this.store.terms, k)) {
              const currentTermGroup = this.store.terms[k];
              // adding term group a empty data to force it to clear all of its term data in the event of deselecting all of the terms in a tax group
              if (currentTermGroup.length === 0) {
                formData.append(`terms[${k}][]`, '');
              } else {
                currentTermGroup.map(d => {
                  formData.append(`terms[${k}][]`, d);
                });
              }
            }
          });
        }

        this.resource
          .save(formData)
          .then(
            resp => resp.json(),
            resp => {
              this.app.submit.isSuccess = false;
              this.submitMessage = resp.message;
            }
          )
          .then(j => {
            if (j.error) {
              throw new Error(j.error);
            }
            this.app.submit.isSuccess = true;
            this.submitMessage = `${j.message || ''} | id: ${j.id}`;
          })
          .catch(e => {
            this.app.submit.isSuccess = false;
            this.submitMessage = e.message;
          });
      } catch (e) {
        this.app.submit.isSuccess = false;
        this.submitMessage = e;
      } finally {
        this.app.submit.fetching = false;
      }
    },
  },
  computed: {
    parsedFields() {
      /**
       * function to filter fields according to supplied filters
       *
       * will be using this function to differentiate fields based on coupon type
       * each coupon type will gonna display the fields that is returned from this function
       * @param main array main data object to be filtered
       * @param filters array filter array with string and regex values
       * @returns {*} array filtered array of fields
       */
      function filterFields(main, filters) {
        return main.filter(m => {
          // eslint-disable-next-line consistent-return
          return filters.filter(f => {
            if (m.id.match(f) !== null) {
              return m;
            }
          })[0];
        });
      }

      // Image type fields
      const imageFilters = [/^link$/, /coupon-image.+/];
      const imageFields = filterFields(this.fields, imageFilters);

      // Coupon type fields
      const couponFilters = [
        'link',
        'coupon-code-text',
        'deal-button-text',
        'discount-text',
        'wpcd_description',
        'show-expiration',
        'expire-date',
        'expire-time',
        'show-expiration',
        'hide-coupon',
        'coupon-template',
        'never-expire-check',
        /.*-theme$/,
      ];
      const couponFields = filterFields(this.fields, couponFilters);

      // Deal type fields
      const dealFilters = [
        'link',
        'deal-button-text',
        'discount-text',
        'wpcd_description',
        'show-expiration',
        'expire-date',
        'expire-time',
        'show-expiration',
        'coupon-template',
        'never-expire-check',
        /.*-theme$/,
      ];
      const dealFields = filterFields(this.fields, dealFilters);

      const finalFields = {
        Image: imageFields,
        Coupon: couponFields,
        Deal: dealFields,
      };

      return finalFields[this.store['coupon-type']];
    },
  },
};
</script>