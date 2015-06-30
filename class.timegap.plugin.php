<?php

$PluginInfo['timegap'] = array(
    'Name' => 'Time Gap',
    'Description' => 'Indicates long time gaps between posts.',
    'Version' => '0.1',
    'HasLocale' => true,
    'MobileFriendly' => true,
    'Author' => 'Bleistivt',
    'AuthorUrl' => 'http://bleistivt.net',
    'License' => 'GNU GPL2'
);

class TimeGapPlugin extends Gdn_Plugin {

    public function assetModel_styleCss_handler($sender) {
        $sender->addCssFile('timegap.css', 'plugins/timegap');
    }

    public function discussionController_beforeCommentDisplay_handler($sender, $args) {
        // Find the previous comment.
        $comments = $sender->data('Comments');
        $comments->dataSeek(-1);
        while ($comment = $comments->nextRow()) {
            if ($comment->CommentID == $args['Comment']->CommentID) break;
        }
        if (!$prev = $comments->previousRow()) {
            $prev = $args['Discussion'];
            if ($sender->data('Page', 1) != 1) {
                $prev = $sender->CommentModel
                    ->get($sender->DiscussionID, 1, $sender->Offset - 1)
                    ->firstRow();
            }
        }

        // Calculate the date difference.
        $date = new DateTime($args['Comment']->DateInserted);
        $gap = $date->diff(new DateTime($prev->DateInserted))->days ?: 0;
        $class = 'TimeGap ';

        if ($gap < 10) {
            return;
        } if ($gap < 30) {
            $gap = sprintf(t('%s days later'), (int)$gap);
            $class .= 'Days';
        } elseif ($gap < 60) {
            $gap = t('1 month later');
            $class .= 'Month';
        } elseif ($gap < 365) {
            $gap = sprintf(t('%s months later'), (int)($gap / 30));
            $class .= 'Months';
        } elseif ($gap < 720) {
            $gap = t('1 year later');
            $class .= 'Year';
        } else {
            $gap = sprintf(t('%s years later'),  (int)($gap / 365));
            $class .= 'Years';
        }
        echo wrap(sprite('Time', 'InformSprite').$gap, 'li', array('class' => $class));
    }

}
