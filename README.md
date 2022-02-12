# Mage2 Module Lof Lof_ProductReviewsGraphQl

    ``landofcoder/module-product-reviews-graphql
``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
magento 2 product reviews graphql extension

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Lof`
 - Enable the module by running `php bin/magento module:enable Lof_ProductReviewsGraphQl`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require landofcoder/module-product-reviews-graphql`
 - enable the module by running `php bin/magento module:enable Lof_ProductReviewsGraphQl`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### TODO
- Refactor Graphql queries
- Refactor Resolvers
- Add documendation for Graphql queries
- Override create product review with extra fields: customize, gallery images
- Complete query resolvers
- Complete create product review reply resolvers

## Queries

Fragment
```
fragment ProductPriceFragment on ProductPrice {
  discount {
    amount_off
    percent_off
  }
  final_price {
    currency
    value
  }
  regular_price {
    currency
    value
  }
}

fragment ProductDetailsFragment on ProductInterface {
  __typename
  categories {
    id
    breadcrumbs {
      category_id
    }
  }
  uid
  url_key
  color
  rating_summary
  price_range {
    maximum_price {
      ...ProductPriceFragment
    }
    minimum_price {
      ...ProductPriceFragment
    }
  }
  created_at
  description {
    html
  }
  id
  media_gallery {
    disabled
    label
    position
    url
  }
  meta_description
  name
  price {
    regularPrice {
      amount {
        currency
        value
      }
    }
  }
  sku
  stock_status
  small_image {
    url
  }
  image {
    url
  }
  url_key
  ... on ConfigurableProduct {
    configurable_options {
      attribute_code
      attribute_id
      id
      label
      values {
        default_label
        label
        store_label
        use_default_value
        value_index
        swatch_data {
          ... on ImageSwatchData {
            thumbnail
          }
          value
        }
      }
    }
    variants {
      attributes {
        code
        value_index
      }
      product {
        id
        media_gallery_entries {
          id
          disabled
          file
          label
          position
        }
        sku
        stock_status
        price {
          regularPrice {
            amount {
              currency
              value
            }
          }
        }
      }
    }
  }
}
```

1. Query filter products with detail reviews information

```
query {
    products(filter: ProductAttributeFilterInput, sort: ProductAttributeSortInput, search: string) {
        aggregations {
            attribute_code
            count
            label
            options {
                count
                label
                value
            }
        }
        sort_fields {
            default
            options {
                label
                value
            }
        }
        total_count
        items {
            ...ProductDetailsFragment
            review_count
            rating_summary
            reviews {
                items {
                    average_rating
                    created_at
                    nickname
                    product {
                        id
                    }
                    ratings_breakdown {
                        name
                        value
                    }
                    summary
                    text
                    customize {
                        advantages
                        disadvantages
                        average
                        count_helpful
                        count_unhelpful
                        total_helpful
                        report_abuse
                    }
                    galleries {
                        id
                        label
                        images
                    }
                    reply {
                        reply_id
                        reply_title
                        reply_comment
                        user_name
                        parent_reply_id
                        website
                        created_at
                    }
                }
            }
        }
    }
}
```

2. Query replies of review

```
query {
  productReviewReplies (
        review_id: Int!,
        filter: ReviewReplyFilterInput,
        pageSize: Int = 5,
        currentPage: Int = 1,
        sort: ReviewReplySortInput
    ) {
        items {
            reply_id
            review_id
            reply_title
            reply_comment
            user_name
            parent_reply_id
            website
            created_at
        }
        total_count
        page_info {
            current_page
            page_size
            total_pages
        }
        sort_fields {
            created_at: DESC
        }
    }
}
```

3. Mutation submit product review with extra information

```
mutation {
    createProductReview (
        input: {
            sku: String!
            nickname: String!
            summary: String!
            text: String!
            ratings: [ProductReviewRatingInput!]!
            email: String
            advantages: String
            disadvantages: String
            images: [ReviewGalleryImageInput]
        }
    ) {
        review {
            review_id
            average_rating
            created_at
            nickname
            product {
                id
            }
            ratings_breakdown {
                name
                value
            }
            summary
            text
            customize {
                advantages
                disadvantages
                average
                count_helpful
                count_unhelpful
                total_helpful
                report_abuse
            }
            galleries {
                id
                label
                images
            }
        }
    }
}
```
