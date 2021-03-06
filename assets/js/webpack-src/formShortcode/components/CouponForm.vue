<template>
  <div class="wpcd-fs-w-full-important">
    <button @click.prevent="$emit('switch', 'UserCoupons')" class="wpcd-fs-pointer">
      {{ extras.strings.back_to_coupons | cap }}
    </button>
    <div
      class="wpcd-fs-flex wpcd-fs-flex-col wpcd-fs-mt-2 wpcd-fs-dynamic-border wpcd-fs-dynamic-bg wpcd-fs-p-4 wpcd-fs-rounded wpcd-fs-shadow"
    >
      <options-component :fields="filterParsedFields(f => f.id !== 'coupon-template')" :coupon-type-data="couponType" />
      <terms-component :taxonomies="extras.terms" />
      <featured-image :featured-url="store.featured_url" />
      <submit-component :message="submitMessage" @submit="submitForm" />
      <message type="error" />
    </div>
    <div class="wpcd-fs-mt-4">
      <template-selector
        :force-disable="!templateSelectionAvailable"
        :templates="filterParsedFields(f => f.id === 'coupon-template')[0]"
      />
      <coupon-preview />
    </div>
  </div>
</template>
<script>
import CouponPreview from './CouponPreview';
import SubmitComponent from './SubmitComponent';
import TermsComponent from './TermsComponent';
import FeaturedImage from './FeaturedImage';
import TemplateSelector from './TemplateSelector';
import OptionsComponent from './OptionsComponent';
import MessageMixin from './mixins/MessageMixin';
import Message from './Message';
import HighlightMixin from './mixins/HighlightMixin';
import logo from '../assets/image/icon-128x128.png';

export default {
  mixins: [MessageMixin, HighlightMixin],
  props: ['fields'],
  components: {
    Message,
    OptionsComponent,
    FeaturedImage,
    TermsComponent,
    CouponPreview,
    TemplateSelector,
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
  mounted() {
    this.forceTemplate();
  },
  methods: {
    forceTemplate() {
      if (!this.store.ID) {
        const currentTemplate = this.extras.options.default_template;
        if (currentTemplate !== 'all') {
          this.store['coupon-template'] = this.extras.options.default_template;
        }
      }
    },
    filterParsedFields(filterCall) {
      return this.parsedFields().filter(filterCall);
    },
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
    submitForm() {
      this.app.submit.fetching = true;
      try {
        const formData = new FormData();
        const data = {
          ...this.store,
          ...{ action: this.extras.options.form_action, nonce: this.extras.options.nonce },
        };

        Object.keys(data).map(k => {
          if (Object.prototype.hasOwnProperty.call(data, k)) {
            formData.set(k, data[k]);
          }
        });

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
              this.setMessage(resp.message, this.messageTypes.error);
            }
          )
          .then(j => {
            if (j.error) {
              throw new Error(j.error);
            }
            this.app.submit.isSuccess = true;
            Object.keys(j.terms).map(key => {
              if (Object.prototype.hasOwnProperty.call(j.terms, key)) {
                this.extras.terms[key] = j.terms[key];
              }
            });

            this.setMessage(`${j.message || ''} | id: ${j.id}`);
            this.highlightId = j.id;
            this.$emit('switch', 'UserCoupons');
          })
          .catch(e => {
            this.setMessage(e.message, this.messageTypes.error);
          });
      } catch (e) {
        this.setMessage(e.message, this.messageTypes.error);
      } finally {
        this.app.submit.fetching = false;
      }
    },
  },
  computed: {
    templateSelectionAvailable() {
      return this.extras.options.default_template === 'all';
    },
  },
};
</script>
