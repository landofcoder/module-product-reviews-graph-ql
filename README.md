# Magento 2 Module Lof Lof_ProductReviewsGraphQl

``landofcoder/module-product-reviews-graphql``

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

## Queries

1. Query get product advanced reviews

* $urlKey : String - product url key
* $search : String - filter by keyword
* $pageSize : Int = 20 - page size
* $currentPage : Int = 1 - current page
* $sortBy : ReviewSortingType = default - enum: default, helpful, rating, latest, oldest, recommended, verified

```
query {
    products(filter: { url_key: { eq: $urlKey } }) {
        items {
            id
            uid
            sku
            advreview (
                search: $search
                pageSize: $pageSize
                currentPage: $currentPage
                sortBy: $sortBy
            ) {
                __typename
                totalRecords
                ratingSummary
                ratingSummaryValue
                recomendedPercent
                totalRecordsFiltered
                detailedSummary {
                    __typename
                    one
                    two
                    three
                    four
                    five
                }
                items {
                    __typename
                    review_id
                    created_at
                    answer
                    verified_buyer
                    is_recommended
                    detail_id
                    title
                    detail
                    nickname
                    like_about
                    not_like_about
                    guest_email
                    plus_review
                    minus_review
                    report_abuse
                    rating_votes {
                        __typename
                        vote_id
                        option_id
                        rating_id
                        review_id
                        percent
                        value
                        rating_code
                    }
                    images {
                        __typename
                        full_path
                        resized_path
                    }
                    comments {
                        __typename
                        id
                        review_id
                        status
                        message
                        nickname
                        email
                        created_at
                        updated_at
                    }
                }
            }
        }
    }
}
```

Example:

```
query {
    products(filter: { url_key: { eq: "pp-009238562599" } }) {
        items {
            id
            uid
            advreview (
                search: ""
                pageSize: 10
                currentPage: 1
                sortBy: helpful
            ) {
                __typename
                totalRecords
                ratingSummary
                ratingSummaryValue
                recomendedPercent
                totalRecordsFiltered
                detailedSummary {
                    __typename
                    one
                    two
                    three
                    four
                    five
                }
                items {
                    __typename
                    review_id
                    created_at
                    answer
                    verified_buyer
                    is_recommended
                    detail_id
                    title
                    detail
                    nickname
                    like_about
                    not_like_about
                    guest_email
                    plus_review
                    minus_review
                    report_abuse
                    rating_votes {
                        __typename
                        vote_id
                        option_id
                        rating_id
                        review_id
                        percent
                        value
                        rating_code
                    }
                    images {
                        __typename
                        full_path
                        resized_path
                    }
                    comments {
                        __typename
                        id
                        review_id
                        status
                        message
                        nickname
                        email
                        created_at
                        updated_at
                    }
                }
            }
        }
    }
}
```

2. Query review comments of a review

```
query {
  comments (
    review_id: Int!
    filter: ReviewCommentFilterInput
    pageSize: Int = 5
    currentPage: Int = 1
    sort: ReviewCommentSortInput
  ) {
      items {
        __typename
        id
        review_id
        status
        message
        nickname
        email
        parent_id
        created_at
        updated_at
      }
      total_count
      page_info {
          page_size
          current_page
          total_pages
      }
  }
}
```

3. Mutation submit like a review

```
mutation {
    likeReview (review_id : Int!)
}
```
4. Mutation submit unlike a review

```
mutation {
    unlikeReview (review_id : Int!)
}
```

5. Mutation submit report a review

```
mutation {
    reportReview (review_id : Int!)
}
```

6. Mutation submit product review with extra information

```
mutation {
    createProductAdvReview (
        input: {
            sku: String!
            nickname: String!
            summary: String!
            text: String!
            ratings: [ProductAdvReviewRatingInput!]!
            email: String
            advantages: String
            disadvantages: String
            images: [ReviewGalleryImageInput]
        }
    ) {
        review {
            review_id
            created_at
            answer
            verified_buyer
            is_recommended
            detail_id
            title
            detail
            nickname
            like_about
            not_like_about
            guest_email
            plus_review
            minus_review
            report_abuse
            rating_votes {
                __typename
                vote_id
                option_id
                rating_id
                review_id
                percent
                value
                rating_code
            }
            images {
                __typename
                full_path
                resized_path
            }
            comments {
                __typename
                id
                review_id
                status
                message
                nickname
                email
                created_at
                updated_at
            }
        }
    }
}
```

- ProductAdvReviewRatingInput:

```
input ProductAdvReviewRatingInput {
    rating_id: Int
    rating_name: String!
    value: Int!
}
```
rating_name: A Rating Name: It is rating_code in table rating. Example Default rating_name: Quality, Value, Price, Rating
value: A Rating number, from 1 to 5.

- ReviewGalleryImageInput:

```
input ReviewGalleryImageInput {
    src: String
}
```

7. Mutation post review comment

```
mutation {
    createComment (
        input : {
            review_id: Int!
            title: String
            message: String!
            nickname: String
            email: String
            parent_id: Int
            website: String
        }
    ) {
        comment {
            id
            review_id
            status
            message
            nickname
            email
            created_at
            updated_at
        }
    }
}
```

Example:

```
mutation {
    createComment (
        input: {
            review_id: 348,
            title: "Comment Title Graphql",
            message: "New message for review from Graphql",
            nickname: "Test User",
            email: "testuser@gmail.com"
        }
    ) {
        comment {
            id
            review_id
            status
            message
            nickname
            email
            created_at
            updated_at
        }
    }
}
```

8. Get logged in customer reviews

Query:

```
{
  customer {
    advreviews(
      filter: {
      }
      pageSize: 5
      currentPage: 1
    ) {
      items {
        review_id
        entity_pk_value
        created_at
        answer
        verified_buyer
        is_recommended
        detail_id
        title
        detail
        nickname
        like_about
        not_like_about
        guest_email
        plus_review
        minus_review
        report_abuse
        rating_votes {
          __typename
          vote_id
          option_id
          rating_id
          review_id
          percent
          value
          rating_code
        }
        images {
          __typename
          full_path
          resized_path
        }
        comments {
          __typename
          id
          review_id
          status
          message
          nickname
          email
          created_at
          updated_at
        }
      }
      page_info {
        page_size
        current_page
        total_pages
      }
      total_count
    }
  }
}
```

Filter fields:

```
review_id
created_at
title
detail
nickname
entity_pk_value
```

Sort fields:

```
review_id
created_at
title
detail
nickname
entity_pk_value
```
