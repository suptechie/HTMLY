<?php

use \Michelf\MarkdownExtra;
use \Suin\RSSWriter\Feed;
use \Suin\RSSWriter\Channel;
use \Suin\RSSWriter\Item;

// Get blog post path. Unsorted. Mostly used on widget.
function get_post_unsorted()
{
    static $_unsorted = array();

    if (empty($_unsorted)) {

        $url = 'cache/index/index-unsorted.txt';
        if (!file_exists($url)) {
            rebuilt_cache('all');
        }
        $_unsorted = unserialize(file_get_contents($url));
    }
    return $_unsorted;
}

// Get blog post with more info about the path. Sorted by filename.
function get_post_sorted()
{
    static $_sorted = array();

    if (empty($_sorted)) {
        $url = 'cache/index/index-sorted.txt';
        if (!file_exists($url)) {
            rebuilt_cache('all');
        }
        $_sorted = unserialize(file_get_contents($url));
    }
    return $_sorted;
}

// Get static page path. Unsorted.
function get_static_pages()
{
    static $_page = array();

    if (empty($_page)) {
        $url = 'cache/index/index-page.txt';
        if (!file_exists($url)) {
            rebuilt_cache('all');
        }
        $_page = unserialize(file_get_contents($url));
    }
    return $_page;
}

// Get static page path. Unsorted.
function get_static_sub_pages($static = null)
{
    static $_sub_page = array();

    if (empty($_sub_page)) {
        $url = 'cache/index/index-sub-page.txt';
        if (!file_exists($url)) {
            rebuilt_cache('all');
        }
        $_sub_page = unserialize(file_get_contents($url));
    }
    if ($static != null) {
        $stringLen = strlen($static);
        return array_filter($_sub_page, function ($sub_page) use ($static, $stringLen) {
            $x = explode("/", $sub_page);
            if ($x[count($x) - 2] == $static) {
                return true;
            }
            return false;
        });
    }
    return $_sub_page;
}

// Get author name. Unsorted.
function get_author_name()
{
    static $_author = array();

    if (empty($_author)) {
        $url = 'cache/index/index-author.txt';
        if (!file_exists($url)) {
            rebuilt_cache('all');
        }
        $_author = unserialize(file_get_contents($url));
    }

    return $_author;
}

// Get backup file.
function get_zip_files()
{
    static $_zip = array();

    if (empty($_zip)) {

        // Get the names of all the
        // zip files.

        $_zip = glob('backup/*.zip');
    }

    return $_zip;
}

// Get user draft.
function get_draft_posts()
{
    static $_draft = array();

    if (empty($_draft)) {
        $tmp = array();
        $tmp = glob('content/*/draft/*.md', GLOB_NOSORT);
        if (is_array($tmp)) {
            foreach ($tmp as $file) {
                $_draft[] = pathinfo($file);
            }
        }
        usort($_draft, "sortfile");
    }
    return $_draft;
}

// usort function. Sort by filename.
function sortfile($a, $b)
{
    return $a['filename'] == $b['filename'] ? 0 : ($a['filename'] < $b['filename']) ? 1 : -1;
}

// usort function. Sort by date.
function sortdate($a, $b)
{
    return $a->date == $b->date ? 0 : ($a->date < $b->date) ? 1 : -1;
}

// Rebuilt cache index
function rebuilt_cache($type)
{
    $dir = 'cache/index';
    $posts_cache_sorted = array();
    $posts_cache_unsorted = array();
    $page_cache = array();
    $author_cache = array();

    if (is_dir($dir) === false) {
        mkdir($dir, 0775, true);
    }

    if ($type === 'posts') {
        $posts_cache_unsorted = glob('content/*/blog/*.md', GLOB_NOSORT);
        $string = serialize($posts_cache_unsorted);
        file_put_contents('cache/index/index-unsorted.txt', print_r($string, true));

        $tmp = array();
        $tmp = glob('content/*/blog/*.md', GLOB_NOSORT);

        if (is_array($tmp)) {
            foreach ($tmp as $file) {
                $posts_cache_sorted[] = pathinfo($file);
            }
        }
        usort($posts_cache_sorted, "sortfile");
        $string = serialize($posts_cache_sorted);
        file_put_contents('cache/index/index-sorted.txt', print_r($string, true));
    } elseif ($type === 'page') {

        $page_cache = glob('content/static/*.md', GLOB_NOSORT);
        $string = serialize($page_cache);
        file_put_contents('cache/index/index-page.txt', print_r($string, true));
    } elseif ($type === 'subpage') {

        $page_cache = glob('content/static/*/*.md', GLOB_NOSORT);
        $string = serialize($page_cache);
        file_put_contents('cache/index/index-sub-page.txt', print_r($string, true));
    } elseif ($type === 'author') {

        $author_cache = glob('content/*/author.md', GLOB_NOSORT);
        $string = serialize($author_cache);
        file_put_contents('cache/index/index-author.txt', print_r($string, true));
    } elseif ($type === 'all') {
        rebuilt_cache('posts');
        rebuilt_cache('page');
        rebuilt_cache('subpage');
        rebuilt_cache('author');
    }
}

// Return blog posts.
function get_posts($posts, $page = 1, $perpage = 0)
{
    if (empty($posts)) {
        $posts = get_post_sorted();
    }

    $tmp = array();

    // Extract a specific page with results
    $posts = array_slice($posts, ($page - 1) * $perpage, $perpage);

    foreach ($posts as $index => $v) {

        $post = new stdClass;

        $filepath = $v['dirname'] . '/' . $v['basename'];

        // Extract the date
        $arr = explode('_', $filepath);

        // Replaced string
        $replaced = substr($arr[0], 0, strrpos($arr[0], '/')) . '/';

        // Author string
        $str = explode('/', $replaced);
        $author = $str[count($str) - 3];

        // The post author + author url
        $post->author = $author;
        $post->authorUrl = site_url() . 'author/' . $author;

        $dt = str_replace($replaced, '', $arr[0]);
        $t = str_replace('-', '', $dt);
        $time = new DateTime($t);
        $timestamp = $time->format("Y-m-d H:i:s");

        // The post date
        $post->date = strtotime($timestamp);

        // The archive per day
        $post->archive = site_url() . 'archive/' . date('Y-m', $post->date);
        
        if (config('permalink.type') == 'post') {
            $post->url = site_url() . 'post/' . str_replace('.md', '', $arr[2]);
        } else {
            $post->url = site_url() . date('Y/m', $post->date) . '/' . str_replace('.md', '', $arr[2]);
        }
        
        $post->file = $filepath;

        $content = file_get_contents($filepath);

        // Extract the title and body
        $post->title = get_content_tag('t', $content, 'Untitled: ' . date('l jS \of F Y', $post->date));
        $post->image = get_content_tag('image', $content);
        $post->video = get_youtube_id(get_content_tag('video', $content));
        $post->link  = get_content_tag('link', $content);
        $post->quote  = get_content_tag('quote', $content);
        $post->audio  = get_content_tag('audio', $content);
        
        $tag = array();
        $url = array();
        $bc = array();
        
        $tagt = get_content_tag('tag', $content);
        $t = explode(',', rtrim($arr[1], ','));
        
        if(!empty($tagt)) {
            $tl = explode(',', rtrim($tagt, ','));
            $tCom = array_combine($t, $tl);
            foreach ($tCom as $key => $val) {
                if(!empty($val)) {
                    $tag[] = array($val, site_url() . 'tag/' . $key);
                } else {
                    $tag[] = array($key, site_url() . 'tag/' . $key);
                }
            } 
        } else {
            foreach ($t as $tt) {
                $tag[] = array($tt, site_url() . 'tag/' . $tt);
            }
        }
        
        foreach ($tag as $a) {
            $url[] = '<span><a href="' . $a[1] . '">' . $a[0] . '</a></span>';
            $bc[] = '<span typeof="v:Breadcrumb"><a property="v:title" rel="v:url" href="' . $a[1] . '">' . $a[0] . '</a></span>';
        }

        $post->tag = implode(', ', $url);

        $post->tagb = implode(' » ', $bc);

        // Get the contents and convert it to HTML
        $post->body = MarkdownExtra::defaultTransform(remove_html_comments($content));

        if (config("views.counter")) {
            $post->views = get_views($post->file);
        }

        $post->description = get_content_tag("d", $content, get_description($post->body));

        $tmp[] = $post;
    }

    return $tmp;
}

// Find post by year, month and name, previous, and next.
function find_post($year, $month, $name)
{
    $posts = get_post_sorted();

    foreach ($posts as $index => $v) {
        $url = $v['basename'];
        if (strpos($url, "$year-$month") !== false && strpos($url, $name . '.md') !== false) {

            // Use the get_posts method to return
            // a properly parsed object

            $ar = get_posts($posts, $index + 1, 1);
            $nx = get_posts($posts, $index, 1);
            $pr = get_posts($posts, $index + 2, 1);

            if ($index == 0) {
                if (isset($pr[0])) {
                    return array(
                        'current' => $ar[0],
                        'prev' => $pr[0]
                    );
                } else {
                    return array(
                        'current' => $ar[0],
                        'prev' => null
                    );
                }
            } elseif (count($posts) == $index + 1) {
                return array(
                    'current' => $ar[0],
                    'next' => $nx[0]
                );
            } else {
                return array(
                    'current' => $ar[0],
                    'next' => $nx[0],
                    'prev' => $pr[0]
                );
            }
        } else if (strpos($url, $name . '.md') !== false) {
            $ar = get_posts($posts, $index + 1, 1);
            $nx = get_posts($posts, $index, 1);
            $pr = get_posts($posts, $index + 2, 1);

            if ($index == 0) {
                if (isset($pr[0])) {
                    return array(
                        'current' => $ar[0],
                        'prev' => $pr[0]
                    );
                } else {
                    return array(
                        'current' => $ar[0],
                        'prev' => null
                    );
                }
            } elseif (count($posts) == $index + 1) {
                return array(
                    'current' => $ar[0],
                    'next' => $nx[0]
                );
            } else {
                return array(
                    'current' => $ar[0],
                    'next' => $nx[0],
                    'prev' => $pr[0]
                );
            }        
        }
    }
}

// Find draft.
function find_draft($year, $month, $name)
{
    $posts = get_draft_posts();

    foreach ($posts as $index => $v) {
        $url = $v['basename'];
        if (strpos($url, "$year-$month") !== false && strpos($url, $name . '.md') !== false) {

            // Use the get_posts method to return
            // a properly parsed object

            $ar = get_posts($posts, $index + 1, 1);
            $nx = get_posts($posts, $index, 1);
            $pr = get_posts($posts, $index + 2, 1);

            if ($index == 0) {
                if (isset($pr[0])) {
                    return array(
                        'current' => $ar[0],
                        'prev' => $pr[0]
                    );
                } else {
                    return array(
                        'current' => $ar[0],
                        'prev' => null
                    );
                }
            } elseif (count($posts) == $index + 1) {
                return array(
                    'current' => $ar[0],
                    'next' => $nx[0]
                );
            } else {
                return array(
                    'current' => $ar[0],
                    'next' => $nx[0],
                    'prev' => $pr[0]
                );
            }
        } else if (strpos($url, $name . '.md') !== false) {
            $ar = get_posts($posts, $index + 1, 1);
            $nx = get_posts($posts, $index, 1);
            $pr = get_posts($posts, $index + 2, 1);

            if ($index == 0) {
                if (isset($pr[0])) {
                    return array(
                        'current' => $ar[0],
                        'prev' => $pr[0]
                    );
                } else {
                    return array(
                        'current' => $ar[0],
                        'prev' => null
                    );
                }
            } elseif (count($posts) == $index + 1) {
                return array(
                    'current' => $ar[0],
                    'next' => $nx[0]
                );
            } else {
                return array(
                    'current' => $ar[0],
                    'next' => $nx[0],
                    'prev' => $pr[0]
                );
            }        
        }
    }
}

// Return tag page.
function get_tag($tag, $page, $perpage, $random)
{
    $posts = get_post_sorted();

    if ($random === true) {
        shuffle($posts);
    }

    $tmp = array();

    foreach ($posts as $index => $v) {
        $url = $v['filename'];
        $str = explode('_', $url);
        $mtag = explode(',', rtrim($str[1], ','));
        $etag = explode(',', $tag);
        foreach ($mtag as $t) {
            foreach ($etag as $e) {
                $e = trim($e);
                if ($t === $e) {
                    $tmp[] = $v;
                }
            }
        }
    }

    if (empty($tmp)) {
        not_found();
    }
    
    $tmp = array_unique($tmp, SORT_REGULAR);

    return $tmp = get_posts($tmp, $page, $perpage);
}

// Return archive page.
function get_archive($req, $page, $perpage)
{
    $posts = get_post_sorted();

    $tmp = array();

    foreach ($posts as $index => $v) {
        $url = $v['filename'];
        $str = explode('_', $url);
        if (strpos($str[0], "$req") !== false) {
            $tmp[] = $v;
        }
    }

    if (empty($tmp)) {
        not_found();
    }

    return $tmp = get_posts($tmp, $page, $perpage);
}

// Return posts list on profile.
function get_profile_posts($name, $page, $perpage)
{
    $posts = get_post_sorted();

    $tmp = array();

    foreach ($posts as $index => $v) {
        $url = $v['dirname'];
        $str = explode('/', $url);
        $author = $str[count($str) - 2];
        if ($name === $author) {
            $tmp[] = $v;
        }
    }

    if (empty($tmp)) {
        return;
    }

    return $tmp = get_posts($tmp, $page, $perpage);
}

// Return draft list
function get_draft($profile, $page, $perpage)
{
    $posts = get_draft_posts();

    $tmp = array();

    foreach ($posts as $index => $v) {
        $url = $v['dirname'];
        $str = explode('/', $url);
        $author = $str[count($str) - 2];
        if ($profile === $author) {
            $tmp[] = $v;
        }
    }

    if (empty($tmp)) {
        return;
    }

    return $tmp = get_posts($tmp, $page, $perpage);
}

// Return author info.
function get_author($name)
{
    $names = get_author_name();

    $username = 'config/users/' . $name . '.ini';

    $tmp = array();

    if (!empty($names)) {

        foreach ($names as $index => $v) {
        
            $author = new stdClass;

            // Replaced string
            $replaced = substr($v, 0, strrpos($v, '/')) . '/';

            // Author string
            $str = explode('/', $replaced);
            $profile = $str[count($str) - 2];

            if ($name === $profile) {
                // Profile URL
                $url = str_replace($replaced, '', $v);
                $author->url = site_url() . 'author/' . $profile;

                // Get the contents and convert it to HTML
                $content = file_get_contents($v);

                // Extract the title and body
                $author->name = get_content_tag('t', $content, $author);
                $author->about = MarkdownExtra::defaultTransform(remove_html_comments($content));

                $tmp[] = $author;
            }
        }
    }

    if (!empty($tmp) || file_exists($username)) {
        return $tmp;
    } else {
        not_found();
    }
}

// Return default profile
function default_profile($name)
{
    $tmp = array();
    $author = new stdClass;

    $author->name = $name;
    $author->about = '<p>Just another HTMLy user.</p>';

    $author->description = 'Just another HTMLy user';

    return $tmp[] = $author;
}

// Return static page.
function get_static_post($static)
{
    $posts = get_static_pages();

    $tmp = array();

    if (!empty($posts)) {

        foreach ($posts as $index => $v) {
            if (strpos($v, $static . '.md') !== false) {

                $post = new stdClass;

                // Replaced string
                $replaced = substr($v, 0, strrpos($v, '/')) . '/';

                // The static page URL
                $url = str_replace($replaced, '', $v);
                $post->url = site_url() . str_replace('.md', '', $url);

                $post->file = $v;

                // Get the contents and convert it to HTML
                $content = file_get_contents($v);

                // Extract the title and body
                $post->title = get_content_tag('t', $content, $static);
                $post->body = MarkdownExtra::defaultTransform(remove_html_comments($content));

                if (config("views.counter")) {
                    $post->views = get_views($post->file);
                }

                $post->description = get_content_tag("d", $content, get_description($post->body));

                $tmp[] = $post;
            }
        }
    }

    return $tmp;
}

// Return static page.
function get_static_sub_post($static, $sub_static)
{
    $posts = get_static_sub_pages($static);

    $tmp = array();

    if (!empty($posts)) {

        foreach ($posts as $index => $v) {
            if (strpos($v, $sub_static . '.md') !== false) {

                $post = new stdClass;

                // Replaced string
                $replaced = substr($v, 0, strrpos($v, '/')) . '/';

                // The static page URL
                $url = str_replace($replaced, '', $v);
                $post->url = site_url() . $static . "/" . str_replace('.md', '', $url);

                $post->file = $v;

                // Get the contents and convert it to HTML
                $content = file_get_contents($v);

                // Extract the title and body
                $post->title = get_content_tag('t', $content, $sub_static);
                $post->body = MarkdownExtra::defaultTransform(remove_html_comments($content));

                $post->views = get_views($post->file);

                $post->description = get_content_tag("d", $content, get_description($post->body));

                $tmp[] = $post;
            }
        }
    }

    return $tmp;
}

// Return search page.
function get_keyword($keyword, $page, $perpage)
{
    $posts = get_post_sorted();

    $tmp = array();

    $words = explode(' ', $keyword);

    foreach ($posts as $index => $v) {
        $arr = explode('_', $v['filename']);
        $filter = $arr[1] . ' ' . $arr[2];
        foreach ($words as $word) {
            if (stripos($filter, $word) !== false) {
                $tmp[] = $v;
            }
        }
    }

    if (empty($tmp)) {
        // a non-existing page
        render('404-search', null, false);
        die;
    }

    return $tmp = get_posts($tmp, $page, $perpage);
}

// Get related posts base on post tag.
function get_related($tag, $custom = null, $count = null)
{

    if (empty($count)) {
        $count = config('related.count');
        if (empty($count)) {
            $count = 3;
        }
    }

    $posts = get_tag(strip_tags(remove_accent($tag)), 1, $count + 1, true);
    $tmp = array();
    $req = urldecode($_SERVER['REQUEST_URI']);

    foreach ($posts as $post) {
        $url = $post->url;
        if (strpos($url, $req) === false) {
            $tmp[] = $post;
        }
    }
    
    if (empty($custom)) {

        $total = count($tmp);

        if ($total >= 1) {

            $i = 1;
            echo '<ul>';
            foreach ($tmp as $post) {
                echo '<li><a href="' . $post->url . '">' . $post->title . '</a></li>';
                if ($i++ >= $count)
                    break;
            }
            echo '</ul>';
        
        } else {
            echo '<ul><li>No related post found</li></ul>';    
        }
    
    } else {
        return $tmp;
    }
    
}

// Return post count. Matching $var and $str provided.
function get_count($var, $str)
{
    $posts = get_post_sorted();

    $tmp = array();

    foreach ($posts as $index => $v) {
        $arr = explode('_', $v[$str]);
        $url = $arr[0];
        if (strpos($url, "$var") !== false) {
            $tmp[] = $v;
        }
    }

    return count($tmp);
}

// Return tag count. Matching $var and $str provided.
function get_tagcount($var, $str)
{
    $posts = get_post_sorted();

    $tmp = array();

    foreach ($posts as $index => $v) {
        $arr = explode('_', $v[$str]);
        $url = $arr[1];
        if (strpos($url, "$var") !== false) {
            $tmp[] = $v;
        }
    }

    return count($tmp);
}

// Return search result count
function keyword_count($keyword)
{
    $posts = get_post_sorted();

    $tmp = array();

    $words = explode(' ', $keyword);

    foreach ($posts as $index => $v) {
        $arr = explode('_', $v['filename']);
        $filter = $arr[1] . ' ' . $arr[2];
        foreach ($words as $word) {
            if (strpos($filter, strtolower($word)) !== false) {
                $tmp[] = $v;
            }
        }
    }

    $tmp = array_unique($tmp, SORT_REGULAR);

    return count($tmp);
}

// Return recent posts lists
function recent_posts($custom = null, $count = null)
{
    
    if (empty($count)) {
        $count = config('recent.count');
        if (empty($count)) {
            $count = 5;
        }
    }
    
    $posts = get_posts(null, 1, $count);
    
    if (!empty($custom)) {
        return $posts;        
    } else {
    
        echo '<ul>';
        foreach ($posts as $post) {
            echo '<li><a href="' . $post->url . '">' . $post->title . '</a></li>';
        }
        if (empty($posts)) {
            echo '<li>No recent posts found</li>';
        }
        echo '</ul>';
    }
}

// Return popular posts lists
function popular_posts($custom = null, $count = null) 
{

    static $_views = array();
    $tmp = array();

    if (empty($count)) {
        $count = config('popular.count');
        if (empty($count)) {
            $count = 5;
        }
    }
    
    if (config('views.counter') == 'true') {
        if (empty($_views)) {
            $filename = 'content/views.json';
            if (file_exists($filename)) {
                $_views = json_decode(file_get_contents($filename), true);
                if(is_array($_views)) {
                    arsort($_views);
                    foreach ($_views as $key => $val) {
                        if (file_exists($key)) {
                            if (strpos($key, 'blog') !== false) {
                                $tmp[] = pathinfo($key);
                            }
                        }
                    }
                    $posts = get_posts($tmp, 1, $count);
                    if (empty($custom)) {
                        echo '<ul>';
                        foreach ($posts as $post) {
                            echo '<li><a href="' . $post->url . '">' . $post->title . '</a></li>';
                        }
                        echo '</ul>';
                    }
                    else {
                        return $posts;
                    }
                } else {
                    if(empty($custom)) {
                        echo '<ul><li>No popular posts found</li></ul>';
                    } else {
                        return $tmp;
                    }
                } 
            } else {
                if (empty($custom)) {
                    echo '<ul><li>No popular posts found</li></ul>';
                } else {
                    return $tmp;
                }
            }
        }
    } else {
        if (empty($custom)) {
            echo '<ul><li>No popular posts found</li></ul>';
        } else {
            return $tmp;
        }
    }
}

// Return an archive list, categorized by year and month.
function archive_list($custom = null)
{
    $posts = get_post_unsorted();
    $by_year = array();
    $col = array();

    if (!empty($posts)) {

        foreach ($posts as $index => $v) {

            $arr = explode('_', $v);

            // Replaced string
            $str = $arr[0];
            $replaced = substr($str, 0, strrpos($str, '/')) . '/';

            $date = str_replace($replaced, '', $arr[0]);
            $data = explode('-', $date);
            $col[] = $data;
        }

        foreach ($col as $row) {

            $y = $row['0'];
            $m = $row['1'];
            $by_year[$y][] = $m;
        }

        # Most recent year first
        krsort($by_year);
        # Iterate for display
        $i = 0;
        $len = count($by_year);
        if (empty($custom)) {
            foreach ($by_year as $year => $months) {
                if ($i == 0) {
                    $class = 'expanded';
                    $arrow = '&#9660;';
                } else {
                   $class = 'collapsed';
                    $arrow = '&#9658;';
                }
                $i++;
            
                $by_month = array_count_values($months);
                # Sort the months
                krsort($by_month);
            
                $script = <<<EOF
                    if (this.parentNode.className.indexOf('expanded') > -1){this.parentNode.className = 'collapsed';this.innerHTML = '&#9658;';} else {this.parentNode.className = 'expanded';this.innerHTML = '&#9660;';}
EOF;
                echo '<ul class="archivegroup">';
                echo '<li class="' . $class . '">';
                echo '<a href="javascript:void(0)" class="toggle" onclick="' . $script . '">' . $arrow . '</a> ';
                echo '<a href="' . site_url() . 'archive/' . $year . '">' . $year . '</a> ';
                echo '<span class="count">(' . count($months) . ')</span>';
                echo '<ul class="month">';

                foreach ($by_month as $month => $count) {
                    $name = date('F', mktime(0, 0, 0, $month, 1, 2010));
                    echo '<li class="item"><a href="' . site_url() . 'archive/' . $year . '-' . $month . '">' . $name . '</a>';
                    echo ' <span class="count">(' . $count . ')</span></li>';
                }

                echo '</ul>';
                echo '</li>';
                echo '</ul>';
            }
        } else {
            return $by_year;
        }
    }
}

// Return tag cloud.
function tag_cloud($custom = null)
{
    $posts = get_post_unsorted();
    $tags = array();

    if (!empty($posts)) {

        foreach ($posts as $index => $v) {

            $arr = explode('_', $v);

            $data = rtrim($arr[1], ',');
            $mtag = explode(',', $data);
            foreach ($mtag as $etag) {
                $tags[] = $etag;
            }
        }

        $tag_collection = array_count_values($tags);
        ksort($tag_collection);
        
        if(empty($custom)) {
            echo '<ul class="taglist">';
            foreach ($tag_collection as $tag => $count) {
                echo '<li class="item"><a href="' . site_url() . 'tag/' . $tag . '">' . tag_i18n($tag) . '</a> <span class="count">(' . $count . ')</span></li>';
            }
            echo '</ul>';
        } else {
            return $tag_collection;
        }
    } else {
        if(empty($custom)) return;
        return $tags;
    }
}

// Helper function to determine whether
// to show the previous buttons
function has_prev($prev)
{
    if (!empty($prev)) {
        return array(
            'url' => $prev->url,
            'title' => $prev->title
        );
    }
}

// Helper function to determine whether
// to show the next buttons
function has_next($next)
{
    if (!empty($next)) {
        return array(
            'url' => $next->url,
            'title' => $next->title
        );
    }
}

// Helper function to determine whether
// to show the pagination buttons
function has_pagination($total, $perpage, $page = 1)
{
    if (!$total) {
        $total = count(get_post_unsorted());
    }
    return array(
        'prev' => $page > 1,
        'next' => $total > $page * $perpage
    );
}

// Get the meta description
function get_description($string, $char = null)
{
    if(empty($char)) {
        $char = config('description.char');
        if(empty($char)) {
            $char = 150;
        }
    }
    if (strlen(strip_tags($string)) < $char) {
        return safe_html(strip_tags($string));
    } else {
        $string = safe_html(strip_tags($string));
        $string = substr($string, 0, $char);
        $string = substr($string, 0, strrpos($string, ' '));
        return $string;
    }

}

// Get the teaser
function get_teaser($string, $char = null)
{
    $teaserType = config('teaser.type');
    
    if(empty($char)) {
        $char = config('teaser.char');
        if(empty($char)) {
            $char = 200;
        }        
    }

    if ($teaserType === 'full') {
        echo $string;
    } elseif (strlen(strip_tags($string)) < $char) {
        $string = preg_replace('/\s\s+/', ' ', strip_tags($string));
        $string = ltrim(rtrim($string));
        return $string;
    } else {
        $string = preg_replace('/\s\s+/', ' ', strip_tags($string));
        $string = ltrim(rtrim($string));
        $string = substr($string, 0, $char);
        $string = substr($string, 0, strrpos($string, ' '));
        return $string;
    }
}

// Get thumbnail from image and Youtube.
function get_thumbnail($text)
{
    if (config('img.thumbnail') == 'true') {

        $teaserType = config('teaser.type');

        if (strlen(strip_tags($text)) > config('teaser.char') && $teaserType === 'trimmed') {

            libxml_use_internal_errors(true);
            $default = config('default.thumbnail');
            $dom = new DOMDocument();
            $dom->loadHtml($text);
            $imgTags = $dom->getElementsByTagName('img');
            $vidTags = $dom->getElementsByTagName('iframe');
            if ($imgTags->length > 0) {
                $imgElement = $imgTags->item(0);
                $imgSource = $imgElement->getAttribute('src');
                return '<div class="thumbnail" style="background-image:url(' . $imgSource . ');"></div>';
            } elseif ($vidTags->length > 0) {
                $vidElement = $vidTags->item(0);
                $vidSource = $vidElement->getAttribute('src');
                $fetch = explode("embed/", $vidSource);
                if (isset($fetch[1])) {
                    $vidThumb = '//img.youtube.com/vi/' . $fetch[1] . '/default.jpg';
                    return '<div class="thumbnail" style="background-image:url(' . $vidThumb . ');"></div>';
                }
            } else {
                if (!empty($default)) {
                    return '<div class="thumbnail" style="background-image:url(' . $default . ');"></div>';
                }
            }
        } else {

        }
    }
}

// Return edit tab on post
function tab($p)
{
    $user = $_SESSION[config("site.url")]['user'];
    $role = user('role', $user);
    if (isset($p->author)) {
        if ($user === $p->author || $role === 'admin') {
            echo '<div class="tab"><ul class="nav nav-tabs"><li role="presentation" class="active"><a href="' . $p->url . '">View</a></li><li><a href="' . $p->url . '/edit?destination=post">Edit</a></li></ul></div>';
        }
    } else {
        echo '<div class="tab"><ul class="nav nav-tabs"><li role="presentation" class="active"><a href="' . $p->url . '">View</a></li><li><a href="' . $p->url . '/edit?destination=post">Edit</a></li></ul></div>';
    }
}

// Use base64 encode image to speed up page load time.
function base64_encode_image($filename = string, $filetype = string)
{
    if ($filename) {
        $imgbinary = fread(fopen($filename, "r"), filesize($filename));
        return 'data:image/' . $filetype . ';base64,' . base64_encode($imgbinary);
    }
}

// Social links
function social($imgDir = null)
{
    $twitter = config('social.twitter');
    $facebook = config('social.facebook');
    $google = config('social.google');
    $tumblr = config('social.tumblr');
    $rss = site_url() . 'feed/rss';

    if ($imgDir === null) {
        $imgDir = "default/img/";
    }

    if (!empty($twitter)) {
        echo '<a href="' . $twitter . '" target="_blank"><img src="' . site_url() . 'themes/' . $imgDir . 'twitter.png" width="32" height="32" alt="Twitter"/></a>';
    }

    if (!empty($facebook)) {
        echo '<a href="' . $facebook . '" target="_blank"><img src="' . site_url() . 'themes/' . $imgDir . 'facebook.png" width="32" height="32" alt="Facebook"/></a>';
    }

    if (!empty($google)) {
        echo '<a href="' . $google . '" target="_blank"><img src="' . site_url() . 'themes/' . $imgDir . 'googleplus.png" width="32" height="32" alt="Google+"/></a>';
    }

    if (!empty($tumblr)) {
        echo '<a href="' . $tumblr . '" target="_blank"><img src="' . site_url() . 'themes/' . $imgDir . 'tumblr.png" width="32" height="32" alt="Tumblr"/></a>';
    }

    echo '<a href="' . $rss . '" target="_blank"><img src="' . site_url() . 'themes/' . $imgDir . 'rss.png" width="32" height="32" alt="RSS Feed"/></a>';
}

// Copyright
function copyright()
{
    $blogcp = blog_copyright();
    $credit = 'Proudly powered by <a href="http://www.htmly.com" target="_blank">HTMLy</a>';

    if (!empty($blogcp)) {
        return $copyright = '<p>' . $blogcp . '</p><p>' . $credit . '</p>';
    } else {
        return $credit = '<p>' . $credit . '</p>';
    }
}

// Disqus on post.
function disqus($title = null, $url = null)
{
    $comment = config('comment.system');
    $disqus = config('disqus.shortname');
    $script = <<<EOF
    <script type="text/javascript">
        var disqus_shortname = '{$disqus}';
        var disqus_title = '{$title}';
        var disqus_url = '{$url}';
        (function () {
            var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
            dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
        })();
    </script>
EOF;
    if (!empty($disqus) && $comment == 'disqus') {
        return $script;
    }
}

// Disqus comment count on teaser
function disqus_count()
{
    $comment = config('comment.system');
    $disqus = config('disqus.shortname');
    $script = <<<EOF
    <script type="text/javascript">
        var disqus_shortname = '{$disqus}';
        (function () {
            var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
            dsq.src = '//' + disqus_shortname + '.disqus.com/count.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
        })();
    </script>
EOF;
    if (!empty($disqus) && $comment == 'disqus') {
        return $script;
    }
}

// Disqus recent comments
function recent_comments()
{
    $comment = config('comment.system');
    $disqus = config('disqus.shortname');
    $script = <<<EOF
        <script type="text/javascript" src="//{$disqus}.disqus.com/recent_comments_widget.js?num_items=5&hide_avatars=0&avatar_size=48&excerpt_length=200&hide_mods=0"></script>
EOF;
    if (!empty($disqus) && $comment == 'disqus') {
        return $script;
    }
}

// Facebook comments
function facebook()
{
    $comment = config('comment.system');
    $appid = config('fb.appid');
    $script = <<<EOF
    <div id="fb-root"></div>
    <script>(function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); 
        js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId={$appid}";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
    </script>
    <style>.fb-comments, .fb_iframe_widget span, .fb-comments iframe {width: 100%!important;}</style>
EOF;

    if (!empty($appid) && $comment == 'facebook') {
        return $script;
    }
}

// Google Publisher (Google+ page).
function publisher()
{
    $publisher = config('google.publisher');
    if (!empty($publisher)) {
        return $publisher;
    }
}

// Google Analytics
function analytics($analyticsDir = null)
{
    $analytics = config('google.analytics.id');
    if ($analyticsDir === null) {
        $analyticsDir = '//www.google-analytics.com/analytics.js';
    } else {
        $analyticsDir = site_url() . 'themes/' . $analyticsDir . 'analytics.js';
    }
    $script = <<<EOF
    <script>
        (function (i,s,o,g,r,a,m) {i['GoogleAnalyticsObject']=r;i[r]=i[r]||function () {
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','{$analyticsDir}','ga');
        ga('create', '{$analytics}', 'auto');
        ga('send', 'pageview');
</script>
EOF;
    if (!empty($analytics)) {
        return $script;
    }
}

// Menu
function menu($custom = null)
{
    $menu = config('blog.menu');
    $req = $_SERVER['REQUEST_URI'];

    if (!empty($menu)) {

        $links = explode('|', $menu);

        echo '<ul class="nav navbar-nav ' . $custom . '">';

        $i = 0;
        $len = count($links);

        foreach ($links as $link) {

            if ($i == 0) {
                $class = 'item first';
            } elseif ($i == $len - 1) {
                $class = 'item last';
            } else {
                $class = 'item';
            }

            $i++;

            $anc = explode('->', $link);

            if (isset($anc[0]) && isset($anc[1])) {

                if (strpos(rtrim($anc[1], '/') . '/', site_url()) !== false) {
                    $id = substr($link, strrpos($link, '/') + 1);
                    $file = 'content/static/' . $id . '.md';
                    if (file_exists($file)) {
                        if (strpos($req, $id) !== false) {
                            echo '<li class="' . $class . ' active"><a href="' . $anc[1] . '">' . $anc[0] . '</a></li>';
                        } else {
                            echo '<li class="' . $class . '"><a href="' . $anc[1] . '">' . $anc[0] . '</a></li>';
                        }
                    } else {
                        if (rtrim($anc[1], '/') . '/' == site_url()) {
                            if ($req == site_path() . '/') {
                                echo '<li class="' . $class . ' active"><a href="' . site_url() . '">' . config('breadcrumb.home') . '</a></li>';
                            } else {
                                echo '<li class="' . $class . '"><a href="' . site_url() . '">' . config('breadcrumb.home') . '</a></li>';
                            }
                        } else {
                            echo '<li class="' . $class . '"><a target="_blank" href="' . $anc[1] . '">' . $anc[0] . '</a></li>';
                        }
                    }
                } else {
                    echo '<li class="' . $class . '"><a target="_blank" href="' . $anc[1] . '">' . $anc[0] . '</a></li>';
                }
            }
        }

        echo '</ul>';
    } else {
        get_menu($custom);
    }
}

// Get the title from file
function get_title_from_file($v)
{
    // Get the contents and convert it to HTML
    $content = MarkdownExtra::defaultTransform(file_get_contents($v));

    $replaced = substr($v, 0, strrpos($v, '/')) . '/';
    $base = str_replace($replaced, '', $v);

    // Extract the title and body
    return get_content_tag('t', $content, str_replace('-', ' ', str_replace('.md', '', $base)));
}

// Auto generate menu from static page
function get_menu($custom)
{
    $posts = get_static_pages();
    $req = $_SERVER['REQUEST_URI'];

    if (!empty($posts)) {

        krsort($posts);

        echo '<ul class="nav navbar-nav ' . $custom . '">';
        if ($req == site_path() . '/') {
            echo '<li class="item first active"><a href="' . site_url() . '">' . config('breadcrumb.home') . '</a></li>';
        } else {
            echo '<li class="item first"><a href="' . site_url() . '">' . config('breadcrumb.home') . '</a></li>';
        }

        $i = 0;
        $len = count($posts);

        foreach ($posts as $index => $v) {

            if ($i == $len - 1) {
                $class = 'item last';
            } else {
                $class = 'item';
            }
            $i++;

            // Replaced string
            $replaced = substr($v, 0, strrpos($v, '/')) . '/';
            $base = str_replace($replaced, '', $v);
            $url = site_url() . str_replace('.md', '', $base);

            $title = get_title_from_file($v);

            if ($req == site_path() . "/" . str_replace('.md', '', $base)) {
                $active = ' active';
                $reqBase = '';
            } else {
                $active = '';
            }
            
            $subPages = get_static_sub_pages(str_replace('.md', '', $base));
            if (!empty($subPages)) {
                echo '<li class="' . $class . $active .' dropdown">';
                echo '<a class="dropdown-toggle" data-toggle="dropdown" href="' . $url . '">' . ucwords($title) . '<b class="caret"></b></a>';
                echo '<ul class="subnav dropdown-menu" role="menu">';
                $iSub = 0;
                $countSub = count($subPages);
                foreach ($subPages as $index => $sp) {
                    $classSub = "item";
                    if ($iSub == 0) {
                        $classSub .= " first";
                    }
                    if ($iSub == $countSub - 1) {
                        $classSub .= " last";
                    }
                    $replacedSub = substr($sp, 0, strrpos($sp, '/')) . '/';
                    $baseSub = str_replace($replacedSub, '', $sp);

                    if ($req == site_path() . "/" . str_replace('.md', '', $base) . "/" . str_replace('.md', '', $baseSub)) {
                        $classSub .= ' active';
                    }
                    $urlSub = $url . "/" . str_replace('.md', '', $baseSub);
                    echo '<li class="' . $classSub . '"><a href="' . $urlSub . '">' . get_title_from_file($sp) . '</a></li>';
                    $iSub++;
                }
                echo '</ul>';
            } else {
                echo '<li class="' . $class . $active .'">';
                echo '<a href="' . $url . '">' . ucwords($title) . '</a>';
            }
            echo '</li>';
        }
        echo '</ul>';
    } else {

        echo '<ul class="nav navbar-nav ' . $custom . '">';
        if ($req == site_path() . '/') {
            echo '<li class="item first active"><a href="' . site_url() . '">' . config('breadcrumb.home') . '</a></li>';
        } else {
            echo '<li class="item first"><a href="' . site_url() . '">' . config('breadcrumb.home') . '</a></li>';
        }
        echo '</ul>';
    }
}

// Search form
function search($text = null)
{
    if(!empty($text)) {
        echo <<<EOF
    <form id="search-form" method="get">
        <input type="text" class="search-input" name="search" value="{$text}" onfocus="if (this.value == '{$text}') {this.value = '';}" onblur="if (this.value == '') {this.value = '{$text}';}">
        <input type="submit" value="{$text}" class="search-button">
    </form>
EOF;
    } else {
        echo <<<EOF
    <form id="search-form" method="get">
        <input type="text" class="search-input" name="search" value="Search" onfocus="if (this.value == 'Search') {this.value = '';}" onblur="if (this.value == '') {this.value = 'Search';}">
        <input type="submit" value="Search" class="search-button">
    </form>
EOF;
    }
    if (isset($_GET['search'])) {
        $search = $_GET['search'];
        $url = site_url() . 'search/' . remove_accent($search);
        header("Location: $url");
    }
}

// The not found error
function not_found()
{
    header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
    render('404', null, false);
    die();
}

// Turn an array of posts into an RSS feed
function generate_rss($posts)
{
    $feed = new Feed();
    $channel = new Channel();
    $rssLength = config('rss.char');

    $channel
        ->title(blog_title())
        ->description(blog_description())
        ->url(site_url())
        ->appendTo($feed);

    foreach ($posts as $p) {

        if (!empty($rssLength)) {
            if (strlen(strip_tags($p->body)) < config('rss.char')) {
                $string = preg_replace('/\s\s+/', ' ', strip_tags($p->body));
                $body = $string . '...';
            } else {
                $string = preg_replace('/\s\s+/', ' ', strip_tags($p->body));
                $string = substr($string, 0, config('rss.char'));
                $string = substr($string, 0, strrpos($string, ' '));
                $body = $string . '...';
            }
        } else {
            $body = $p->body;
        }

        $item = new Item();
        $tags = explode(',', str_replace(' ', '', strip_tags($p->tag)));
        foreach ($tags as $tag) {
            $item
                ->category($tag, site_url() . 'tag/' . $tag);
        }
        $item
            ->title($p->title)
            ->pubDate($p->date)
            ->description($body)
            ->url($p->url)
            ->appendTo($channel);
    }

    echo $feed;
}

// Return post, archive url for sitemap
function sitemap_post_path()
{
    $posts = get_post_sorted();

    $tmp = array();

    foreach ($posts as $index => $v) {

        $post = new stdClass;

        $filepath = $v['dirname'] . '/' . $v['basename'];

        // Extract the date
        $arr = explode('_', $filepath);

        // Replaced string
        $replaced = substr($arr[0], 0, strrpos($arr[0], '/')) . '/';

        // Author string
        $str = explode('/', $replaced);
        $author = $str[count($str) - 3];

        $post->authorUrl = site_url() . 'author/' . $author;

        $dt = str_replace($replaced, '', $arr[0]);
        $t = str_replace('-', '', $dt);
        $time = new DateTime($t);
        $timestamp = $time->format("Y-m-d H:i:s");

        // The post date
        $post->date = strtotime($timestamp);

        // The archive per day
        $post->archiveday = site_url() . 'archive/' . date('Y-m-d', $post->date);

        // The archive per day
        $post->archivemonth = site_url() . 'archive/' . date('Y-m', $post->date);

        // The archive per day
        $post->archiveyear = site_url() . 'archive/' . date('Y', $post->date);

        // The post URL
        if (config('permalink.type') == 'post') {
            $post->url = site_url() . 'post/' . str_replace('.md', '', $arr[2]);
        } else {
            $post->url = site_url() . date('Y/m', $post->date) . '/' . str_replace('.md', '', $arr[2]);
        }

        $tmp[] = $post;
    }

    return $tmp;
}

// Return static page path for sitemap
function sitemap_page_path()
{
    $posts = get_static_pages();

    $tmp = array();

    if (!empty($posts)) {

        foreach ($posts as $index => $v) {

            $post = new stdClass;

            // Replaced string
            $replaced = substr($v, 0, strrpos($v, '/')) . '/';

            // The static page URL
            $url = str_replace($replaced, '', $v);
            $post->url = site_url() . str_replace('.md', '', $url);

            $tmp[] = $post;
        }
    }

    return $tmp;
}

// Generate sitemap.xml.
function generate_sitemap($str)
{
    header('X-Robots-Tag: noindex');

    echo '<?xml version="1.0" encoding="UTF-8"?>';

    if ($str == 'index') {

        echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        echo '<sitemap><loc>' . site_url() . 'sitemap.base.xml</loc></sitemap>';
        echo '<sitemap><loc>' . site_url() . 'sitemap.post.xml</loc></sitemap>';
        echo '<sitemap><loc>' . site_url() . 'sitemap.static.xml</loc></sitemap>';
        echo '<sitemap><loc>' . site_url() . 'sitemap.tag.xml</loc></sitemap>';
        echo '<sitemap><loc>' . site_url() . 'sitemap.archive.xml</loc></sitemap>';
        echo '<sitemap><loc>' . site_url() . 'sitemap.author.xml</loc></sitemap>';
        echo '</sitemapindex>';
    } elseif ($str == 'base') {

        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        echo '<url><loc>' . site_url() . '</loc><priority>1.0</priority></url>';
        echo '</urlset>';
    } elseif ($str == 'post') {

        $posts = sitemap_post_path();

        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($posts as $p) {
            echo '<url><loc>' . $p->url . '</loc><priority>0.5</priority></url>';
        }

        echo '</urlset>';
    } elseif ($str == 'static') {

        $posts = sitemap_page_path();

        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        if (!empty($posts)) {

            foreach ($posts as $p) {
                echo '<url><loc>' . $p->url . '</loc><priority>0.5</priority></url>';
            }
        }

        echo '</urlset>';
    } elseif ($str == 'tag') {

        $posts = get_post_unsorted();
        $tags = array();

        if (!empty($posts)) {
            foreach ($posts as $index => $v) {

                $arr = explode('_', $v);

                $data = $arr[1];
                $mtag = explode(',', $data);
                foreach ($mtag as $etag) {
                    $tags[] = $etag;
                }
            }

            foreach ($tags as $t) {
                $tag[] = site_url() . 'tag/' . $t;
            }

            echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            if (isset($tag)) {

                $tag = array_unique($tag, SORT_REGULAR);

                foreach ($tag as $t) {
                    echo '<url><loc>' . $t . '</loc><priority>0.5</priority></url>';
                }
            }

            echo '</urlset>';
        }
    } elseif ($str == 'archive') {

        $posts = sitemap_post_path();
        $day = array();
        $month = array();
        $year = array();

        foreach ($posts as $p) {
            $day[] = $p->archiveday;
            $month[] = $p->archivemonth;
            $year[] = $p->archiveyear;
        }

        $day = array_unique($day, SORT_REGULAR);
        $month = array_unique($month, SORT_REGULAR);
        $year = array_unique($year, SORT_REGULAR);

        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($day as $d) {
            echo '<url><loc>' . $d . '</loc><priority>0.5</priority></url>';
        }

        foreach ($month as $m) {
            echo '<url><loc>' . $m . '</loc><priority>0.5</priority></url>';
        }

        foreach ($year as $y) {
            echo '<url><loc>' . $y . '</loc><priority>0.5</priority></url>';
        }

        echo '</urlset>';
    } elseif ($str == 'author') {

        $posts = sitemap_post_path();
        $author = array();

        foreach ($posts as $p) {
            $author[] = $p->authorUrl;
        }

        $author = array_unique($author, SORT_REGULAR);

        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($author as $a) {
            echo '<url><loc>' . $a . '</loc><priority>0.5</priority></url>';
        }

        echo '</urlset>';
    }
}

// Function to generate OPML file
function generate_opml()
{
    $opml_data = array(
        'head' => array(
            'title' => blog_title() . ' OPML File',
            'ownerName' => blog_title(),
            'ownerId' => site_url()
        ),
        'body' => array(
            array(
                'text' => blog_title(),
                'description' => blog_description(),
                'htmlUrl' => site_url(),
                'language' => 'unknown',
                'title' => blog_title(),
                'type' => 'rss',
                'version' => 'RSS2',
                'xmlUrl' => site_url() . 'feed/rss'
            )
        )
    );

    $opml = new opml($opml_data);
    echo $opml->render();
}

// Turn an array of posts into a JSON
function generate_json($posts)
{
    return json_encode($posts);
}

// Create Zip files
function Zip($source, $destination, $include_dir = false)
{
    if (!extension_loaded('zip') || !file_exists($source)) {
        return false;
    }

    if (file_exists($destination)) {
        unlink($destination);
    }

    $zip = new ZipArchive();

    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
        return false;
    }

    if (is_dir($source) === true) {

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file) {
            $file = str_replace('\\', '/', $file);

            // Ignore "." and ".." folders
            if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
                continue;

            if (is_dir($file) === true) {
                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
            } elseif (is_file($file) === true) {
                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
            }
        }
    } elseif (is_file($source) === true) {
        $zip->addFromString(basename($source), file_get_contents($source));
    }

    return $zip->close();
}

// TRUE if the current page is an index page like frontpage, tag index, archive index and search index.
function is_index()
{
    $req = $_SERVER['REQUEST_URI'];
    if (strpos($req, '/archive/') !== false || strpos($req, '/tag/') !== false || strpos($req, '/search/') !== false || strpos($req, '/blog') !== false || $req == site_path() . '/' || strpos($req, site_path() . '/?page') !== false) {
        return true;
    } else {
        return false;
    }
}

// TRUE if the current page is the front page.
function is_front($value = null)
{
    if (!empty($value)) {
        return true;
    } else {
        return false;
    }
}

// TRUE if the current page is the blog page.
function is_blog($value = null)
{
    if (!empty($value)) {
        return true;
    } else {
        return false;
    }
}

// TRUE if the current page is tag index.
function is_tag($value = null)
{
    if (!empty($value)) {
        return true;
    } else {
        return false;
    }
}

// TRUE if the current page is archive index.
function is_archive($value = null)
{
    if (!empty($value)) {
        return true;
    } else {
        return false;
    }
}

// TRUE if the current page is search index.
function is_search($value = null)
{
    if (!empty($value)) {
        return true;
    } else {
        return false;
    }
}

// TRUE if the current page is profile page.
function is_profile($value = null)
{
    if (!empty($value)) {
        return true;
    } else {
        return false;
    }
}

// TRUE if the current page is post page.
function is_post($value = null)
{
    if (!empty($value)) {
        return true;
    } else {
        return false;
    }
}

// TRUE if the current page is static page page.
function is_page($value = null)
{
    if (!empty($value)) {
        return true;
    } else {
        return false;
    }
}

// TRUE if the current page is sub static page.
function is_subpage($value = null)
{
    if (!empty($value)) {
        return true;
    } else {
        return false;
    }
}

// Return blog title
function blog_title()
{
    return config('blog.title');
}

// Return blog tagline
function blog_tagline()
{
    return config('blog.tagline');
}

// Return blog description
function blog_description()
{
    return config('blog.description');
}

// Return blog copyright
function blog_copyright()
{
    return config('blog.copyright');
}

// Return author info. Deprecated
function authorinfo($name = null, $about = null)
{
    if (config('author.info') == 'true') {
        return '<div class="author-info"><h4>by <strong>' . $name . '</strong></h4>' . $about . '</div>';
    }
}

// Output head contents
function head_contents()
{
    $output = '';
    $wmt_id = config('google.wmt.id');

    $favicon = '<link rel="icon" type="image/x-icon" href="' . site_url() . 'favicon.ico" />';
    $charset = '<meta charset="utf-8" />';
    $generator = '<meta name="generator" content="htmly" />';
    $xua = '<meta http-equiv="X-UA-Compatible" content="IE=edge" />';
    $viewport = '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />';
    $sitemap = '<link rel="sitemap" href="' . site_url() . 'sitemap.xml" />';
    $feed = '<link rel="alternate" type="application/rss+xml" title="' . blog_title() . ' Feed" href="' . site_url() . 'feed/rss" />';
    $webmasterTools = '';
    if (!empty($wmt_id)) {
        $webmasterTools = '<meta name="google-site-verification" content="' . $wmt_id . '" />';
    }

    $output .= $charset . "\n" . $xua . "\n" . $viewport . "\n" . $generator . "\n" . $favicon . "\n" . $sitemap . "\n" . $feed . "\n" . $webmasterTools . "\n";

    return $output;
}

// Return toolbar
function toolbar()
{
    $user = $_SESSION[config("site.url")]['user'];
    $role = user('role', $user);
    $base = site_url();

    echo <<<EOF
    <link href="{$base}themes/default/css/toolbar.css" rel="stylesheet" />
EOF;
    echo '<div id="toolbar"><ul>';
    echo '<li><a href="' . $base . 'admin">Admin</a></li>';
    if ($role === 'admin') {
        echo '<li><a href="' . $base . 'admin/posts">Posts</a></li>';
        if (config('views.counter') == 'true') {
            echo '<li><a href="' . $base . 'admin/popular">Popular</a></li>';
        }
    }
    echo '<li><a href="' . $base . 'admin/mine">Mine</a></li>';
    echo '<li><a href="' . $base . 'admin/draft">Draft</a></li>';
    echo '<li><a href="' . $base . 'admin/content">Add content</a></li>';
    echo '<li><a href="' . $base . 'edit/profile">Edit profile</a></li>';
    echo '<li><a href="' . $base . 'admin/import">Import</a></li>';
    echo '<li><a href="' . $base . 'admin/backup">Backup</a></li>';
    echo '<li><a href="' . $base . 'admin/config">Config</a></li>';
    echo '<li><a href="' . $base . 'admin/clear-cache">Clear cache</a></li>';
    echo '<li><a href="' . $base . 'admin/update">Update</a></li>';
    echo '<li><a href="' . $base . 'logout">Logout</a></li>';

    echo '</ul></div>';
}

// File cache
function file_cache($request)
{
    if (config('cache.off')) return;

    $c = str_replace('/', '#', str_replace('?', '~', $request));
    $cachefile = 'cache/page/' . $c . '.cache';

    if (file_exists($cachefile)) {
        header('Content-type: text/html; charset=utf-8');
        readfile($cachefile);
        die;
    }
}

// Generate csrf token
function generate_csrf_token()
{
    $_SESSION[config("site.url")]['csrf_token'] = sha1(microtime(true) . mt_rand(10000, 90000));
}

// Get csrf token
function get_csrf()
{
    if (!isset($_SESSION[config("site.url")]['csrf_token']) || empty($_SESSION[config("site.url")]['csrf_token'])) {
        generate_csrf_token();
    }
    return $_SESSION[config("site.url")]['csrf_token'];
}

// Check the csrf token
function is_csrf_proper($csrf_token)
{
    if ($csrf_token == get_csrf()) {
        return true;
    }
    return false;
}

// Add page views count
function add_view($page)
{
    $filename = "content/views.json";
    $views = array();
    if (file_exists($filename)) {
        $views = json_decode(file_get_contents($filename), true);
    }
    if (isset($views[$page])) {
        $views[$page]++;
    } else {
        $views[$page] = 1;
    }
    file_put_contents($filename, json_encode($views));
}

// Get the page views count
function get_views($page)
{
    static $_views = array();

    if (empty($_views)) {
        $filename = "content/views.json";
        if (file_exists($filename)) {
            $_views = json_decode(file_get_contents($filename), true);
        }
    }
    if (isset($_views[$page])) {
        return $_views[$page];
    }
    return -1;
}

// Get tag inside the markdown files
function get_content_tag($tag, $string, $alt = null)
{
    $reg = '/\<!--' . $tag . '(.+)' . $tag . '--\>/';
    $ary = array();
    if (preg_match($reg, $string, $ary)) {
        if (isset($ary[1])) {
            $result = trim($ary[1]);
            if (!empty($result)) {
                return $result;
            }
        }
    }
    return $alt;
}

// Strip html comment 
function remove_html_comments($content)
{
    return trim(preg_replace('/(\s|)<!--(.*)-->(\s|)/', '', $content));
}

// Google recaptcha
function isCaptcha($reCaptchaResponse)
{
    if (!config("google.reCaptcha")) {
        return true;
    }
    $url = "https://www.google.com/recaptcha/api/siteverify";
    $options = array(
        "secret" => config("google.reCaptcha.private"),
        "response" => $reCaptchaResponse,
        "remoteip" => $_SERVER['REMOTE_ADDR'],
    );
    $fileContent = @file_get_contents($url . "?" . http_build_query($options));
    if ($fileContent === false) {
        return false;
    }
    $json = json_decode($fileContent, true);
    if ($json == false) {
        return false;
    }
    return ($json['success']);
}

// Get YouTube video ID
function get_youtube_id($url)
{
    if(empty($url)) {
       return;
    }

    preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $url, $matches);

    return $matches[1];
}

// Shorten the string
function shorten($string = null, $char = null)
{
    if(empty($char) || empty($string)) {
        return;
    }
    
    if (strlen(strip_tags($string)) < $char) {
        $string = preg_replace('/\s\s+/', ' ', strip_tags($string));
        $string = ltrim(rtrim($string));
        return $string;
    } else {
        $string = preg_replace('/\s\s+/', ' ', strip_tags($string));
        $string = ltrim(rtrim($string));
        $string = substr($string, 0, $char);
        $string = substr($string, 0, strrpos($string, ' '));
        return $string;
    }
    
}

// save the i18n tag
function save_tag_i18n($tag,$tagDisplay)
{
    $filename = "content/tags.lang";
    $tags = array();
    $tmp = array();
    $views = array();
    
    $tt = explode(',', rtrim($tag, ','));
    $tl = explode(',', rtrim($tagDisplay, ','));
    $tags = array_combine($tt,$tl);
    
    if (file_exists($filename)) {
        $views = unserialize(file_get_contents($filename));
        foreach ($tags as $key => $val) {
            if (isset($views[$key])) {
                $views[$key] = $val;
            } else {
                $views[$key] = $val;
            }
        }
    } else {
        $views = $tags;
    }

    $tmp = serialize($views);
    file_put_contents($filename, print_r($tmp, true));

}

// translate tag to i18n
function tag_i18n($tag)
{
    static $tags = array();

    if (empty($tags)) {
        $filename = "content/tags.lang";
        if (file_exists($filename)) {
            $tags = unserialize(file_get_contents($filename));
        }
    }
    if (isset($tags[$tag])) {
        return $tags[$tag];
    }
    return $tag;
}

// return html safe string
function safe_html($string)
{
    $string = htmlspecialchars($string, ENT_QUOTES);
    $string = preg_replace('/\r\n|\r|\n/', ' ', $string);
    $string = preg_replace('/\s\s+/', ' ', $string);
    $string = ltrim(rtrim($string));
    return $string;
}