<?php

class TimeGapPlugin extends Gdn_Plugin {

    private $previous = false;


    public function assetModel_styleCss_handler($sender) {
        $sender->addCssFile('timegap.css', 'plugins/timegap');
    }


    public function discussionController_beforeCommentDisplay_handler($sender, $args) {
        // Find the previous comment.
        if (!$this->previous) {
            $this->previous = $args['Discussion'];
            if ($sender->data('Page', 1) != 1) {
                $this->previous = $sender->CommentModel
                    ->get($sender->DiscussionID, 1, $sender->Offset - 1)
                    ->firstRow();
            }
        }

        // Calculate the date difference.
        $date = new DateTime($args['Comment']->DateInserted);
        $gap = $date->diff(new DateTime($this->previous->DateInserted))->days;
        $this->previous = $args['Comment'];

        if ($gap < c('TimeGap.MinDays', 7) - 1) {
            return;
        } elseif ($gap < 30) {
            $gap = sprintf(t('%s days later'), (int)$gap);
            $class = 'Days';
        } elseif ($gap < 60) {
            $gap = t('1 month later');
            $class = 'Month';
        } elseif ($gap < 365) {
            $gap = sprintf(t('%s months later'), (int)($gap / 30));
            $class = 'Months';
        } elseif ($gap < 720) {
            $gap = t('1 year later');
            $class = 'Year';
        } else {
            $gap = sprintf(t('%s years later'),  (int)($gap / 365));
            $class = 'Years';
        }

        echo wrap($gap, 'li', ['class' => 'TimeGap '.$class]);
    }

}
