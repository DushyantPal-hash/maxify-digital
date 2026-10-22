<?php if (defined('_NCMAZ_FRONTEND_VERSION')) : ?>

  <div class="bg-primary-50 dark:bg-neutral-800 absolute top-0 inset-x-0 h-60 w-full"></div>

  <header class="entry-header container entry-header--style-audio relative ">
    <div class="bg-white dark:bg-neutral-900 shadow-2xl px-5 py-7 md:p-11 rounded-2xl md:rounded-[40px] flex flex-col sm:flex-row items-center justify-center space-y-10 sm:space-y-0 sm:space-x-11">

      <div class="w-1/2 sm:w-1/4 flex-shrink-0">
        <?php
        $graphql = graphql([
          'query' => '{
                    post(id: "' . get_the_ID() . '", idType: DATABASE_ID) {
                        id
                        link
                        author {
                          node {
                            id
                            avatar {
                              default
                              extraAttr
                              forceDefault
                              foundAvatar
                              height
                              size
                              url
                              width
                            }
                            url
                            uri
                            username
                            name
                            slug
                          }
                        }
                        categories {
                          edges {
                            node {
                              id
                              link
                              name
                              uri
                              slug
                              count
                              categoryId
                            }
                          }
                        }
                        commentCount
                        date
                        excerpt
                        featuredImage {
                          node {
                            id
                            altText
                            caption
                            sourceUrl
                          }
                        }
                        postFormats {
                          edges {
                            node {
                              id
                              name
                              slug
                            }
                          }
                        }
                        postId
                        slug
                        title
                        ncmazVideoUrl {
                          fieldGroupName
                          videoUrl
                        }
                        ncmazAudioUrl {
                          fieldGroupName
                          audioUrl
                        }
                        ncPostMetaData {
                          favoriteButtonShortcode
                          readingTimeShortcode
                          viewsCount
                          fieldGroupName
                        }
                      }
                }'
        ]);

        if ($graphql['data']['post'] !== null) :
          $HeaderSingleAudioProps = (object)[];
          $HeaderSingleAudioProps->postData = $graphql['data']['post'];
          $HeaderSingleAudioProps->featuredImage = get_the_post_thumbnail_url(get_the_ID(), 'medium');
        ?>
          <div data-is-react-component="HeaderSingleAudio" data-component-props="<?php echo esc_attr(json_encode($HeaderSingleAudioProps)); ?>"></div>
        <?php endif; ?>
      </div>

      <div class="flex flex-col space-y-5 flex-grow">
        <?php get_template_part('template-parts/single-header/single-header-content', null, ['has_excerpt' => 0]); ?>
      </div>
    </div>
  </header><!-- .entry-header -->

<?php endif; ?>