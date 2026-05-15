<?php
// database/seeders/DemoUserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Create demo user ──────────────────────────────────────────────
        $user = User::updateOrCreate(
            ['email' => 'demo@journvo.com'],
            [
                'name'     => 'Alex Rivera',
                'password' => Hash::make('demo1234'),
            ]
        );

        $today = Carbon::now();

        // ── 30 days of data ───────────────────────────────────────────────
        $days = [
            30 => [
                'morning' => ['mood' => 'neutral',  'keywords' => ['coffee', 'commute', 'email', 'routine'],        'summary' => 'Alex started the day with a slow morning coffee before heading into a packed commute. The inbox was overflowing but spirits were steady.'],
                'noon'    => ['mood' => 'positive', 'keywords' => ['lunch', 'team', 'project', 'progress'],          'summary' => 'A productive lunch meeting moved the project forward in ways Alex hadn\'t expected. The team energy was contagious.'],
                'evening' => null,
            ],
            29 => [
                'morning' => ['mood' => 'negative', 'keywords' => ['tired', 'alarm', 'rushed', 'late'],              'summary' => 'Overslept and rushed through the morning. Alex felt behind before the day even started.'],
                'noon'    => null,
                'evening' => ['mood' => 'neutral',  'keywords' => ['dinner', 'family', 'quiet', 'rest'],             'summary' => 'The evening was quiet and grounding. A simple home dinner with family helped Alex reset after the rough start.'],
            ],
            28 => [
                'morning' => ['mood' => 'positive', 'keywords' => ['run', 'sunrise', 'energy', 'clear'],             'summary' => 'An early morning run under a vivid sunrise left Alex feeling energized and clear-headed.'],
                'noon'    => ['mood' => 'positive', 'keywords' => ['presentation', 'feedback', 'proud', 'client'],   'summary' => 'The big presentation went better than expected. The client\'s positive feedback made all the preparation feel worthwhile.'],
                'evening' => ['mood' => 'positive', 'keywords' => ['celebrate', 'friends', 'dinner', 'joy'],         'summary' => 'Celebrated the successful presentation with friends over dinner. The conversation flowed and laughter was plentiful.'],
            ],
            27 => [
                'morning' => null,
                'noon'    => ['mood' => 'neutral',  'keywords' => ['reading', 'coffee', 'ideas', 'notes'],           'summary' => 'Spent lunch reading and jotting down ideas in a notebook. A quiet, thoughtful midday break.'],
                'evening' => ['mood' => 'positive', 'keywords' => ['movie', 'relax', 'couch', 'snacks'],             'summary' => 'A relaxed evening watching a film with snacks on the couch. Alex needed this kind of stillness.'],
            ],
            26 => [
                'morning' => ['mood' => 'negative', 'keywords' => ['headache', 'stress', 'deadline', 'pressure'],   'summary' => 'Woke up with a tension headache and a looming deadline. The morning felt heavy and overwhelming.'],
                'noon'    => ['mood' => 'negative', 'keywords' => ['meeting', 'conflict', 'frustrated', 'tense'],   'summary' => 'A difficult team meeting led to some conflict. Alex left feeling frustrated and unheard.'],
                'evening' => ['mood' => 'neutral',  'keywords' => ['walk', 'fresh', 'air', 'calm'],                 'summary' => 'An evening walk in the cool air helped clear the tension. Things feel a bit lighter now.'],
            ],
            25 => [
                'morning' => ['mood' => 'neutral',  'keywords' => ['planning', 'week', 'goals', 'list'],            'summary' => 'Spent the morning planning out the rest of the week. Writing things down always makes them feel more manageable.'],
                'noon'    => null,
                'evening' => null,
            ],
            24 => [
                'morning' => ['mood' => 'positive', 'keywords' => ['yoga', 'mindful', 'grateful', 'peace'],         'summary' => 'A morning yoga session set a peaceful tone. Alex felt genuinely grateful for the small things today.'],
                'noon'    => ['mood' => 'positive', 'keywords' => ['lunch', 'colleague', 'laugh', 'fun'],           'summary' => 'Lunch with a colleague turned into an hour of laughter and good conversation.'],
                'evening' => ['mood' => 'positive', 'keywords' => ['accomplished', 'finished', 'proud', 'relief'],  'summary' => 'Finished a project that had been dragging on. The sense of accomplishment was immense.'],
            ],
            23 => [
                'morning' => ['mood' => 'neutral',  'keywords' => ['gray', 'rain', 'slow', 'tea'],                  'summary' => 'A gray rainy morning. Alex moved slowly and made tea — a small but meaningful change of pace.'],
                'noon'    => ['mood' => 'negative', 'keywords' => ['cancelled', 'disappointed', 'plans', 'alone'],  'summary' => 'Plans for lunch got cancelled last minute. The disappointment lingered through the afternoon.'],
                'evening' => ['mood' => 'neutral',  'keywords' => ['journaling', 'thoughts', 'process', 'write'],   'summary' => 'Spent the evening journaling and processing the day. Writing it out always helps.'],
            ],
            22 => [
                'morning' => null,
                'noon'    => null,
                'evening' => ['mood' => 'positive', 'keywords' => ['concert', 'music', 'alive', 'electric'],        'summary' => 'Attended a live concert in the evening. The energy in the crowd was electric and Alex felt truly alive.'],
            ],
            21 => [
                'morning' => ['mood' => 'positive', 'keywords' => ['fresh', 'new', 'motivated', 'start'],           'summary' => 'Woke up feeling refreshed and motivated. Something about the morning light made everything feel new.'],
                'noon'    => ['mood' => 'positive', 'keywords' => ['gym', 'strength', 'progress', 'sweat'],         'summary' => 'Hit a new personal record at the gym. The hard work is starting to show.'],
                'evening' => ['mood' => 'positive', 'keywords' => ['cooking', 'creative', 'home', 'warm'],          'summary' => 'Tried a new recipe at home. The creative process of cooking was therapeutic.'],
            ],
            20 => [
                'morning' => ['mood' => 'negative', 'keywords' => ['anxious', 'worried', 'exam', 'pressure'],       'summary' => 'Woke up anxious about the upcoming review. The worry sat heavily in the chest all morning.'],
                'noon'    => ['mood' => 'neutral',  'keywords' => ['study', 'prepare', 'focus', 'review'],          'summary' => 'Spent lunch reviewing notes and preparing. The focus helped push the anxiety aside for a while.'],
                'evening' => ['mood' => 'positive', 'keywords' => ['done', 'relief', 'reward', 'breathe'],          'summary' => 'The review went well. Alex let out a long exhale and treated himself to a proper reward dinner.'],
            ],
            19 => [
                'morning' => ['mood' => 'neutral',  'keywords' => ['average', 'routine', 'quiet', 'usual'],         'summary' => 'An average morning with nothing particularly noteworthy. Sometimes ordinary is exactly what\'s needed.'],
                'noon'    => null,
                'evening' => ['mood' => 'neutral',  'keywords' => ['book', 'reading', 'chapter', 'tea'],            'summary' => 'Read several chapters of a book before bed. A gentle close to a gentle day.'],
            ],
            18 => [
                'morning' => ['mood' => 'positive', 'keywords' => ['sunshine', 'window', 'birds', 'bright'],        'summary' => 'Woke to sunshine pouring through the window. Mornings like this are rare and beautiful.'],
                'noon'    => ['mood' => 'positive', 'keywords' => ['park', 'picnic', 'friends', 'outdoor'],         'summary' => 'A spontaneous park picnic with friends. Simple food and great company made for a perfect afternoon.'],
                'evening' => ['mood' => 'positive', 'keywords' => ['content', 'full', 'grateful', 'peace'],         'summary' => 'Ended the day feeling genuinely content. Alex sat on the balcony watching the sky darken, grateful.'],
            ],
            17 => [
                'morning' => ['mood' => 'negative', 'keywords' => ['sick', 'cold', 'tired', 'sore'],                'summary' => 'Woke up with a sore throat and body aches. Staying in bed felt like the only reasonable option.'],
                'noon'    => null,
                'evening' => ['mood' => 'negative', 'keywords' => ['fever', 'rest', 'soup', 'difficult'],           'summary' => 'Still feeling unwell. Managed some soup and plenty of rest. Hoping tomorrow brings improvement.'],
            ],
            16 => [
                'morning' => ['mood' => 'neutral',  'keywords' => ['recovering', 'slow', 'better', 'gentle'],       'summary' => 'Feeling a little better. Moving slowly and gently through the morning, not pushing too hard.'],
                'noon'    => ['mood' => 'neutral',  'keywords' => ['light', 'meal', 'rest', 'gradual'],             'summary' => 'Had a light meal and rested some more. The body is recovering at its own pace.'],
                'evening' => ['mood' => 'positive', 'keywords' => ['improved', 'energy', 'hopeful', 'back'],        'summary' => 'Energy started coming back by evening. Alex felt hopeful and ready to return to normal tomorrow.'],
            ],
            15 => [
                'morning' => ['mood' => 'positive', 'keywords' => ['back', 'strong', 'ready', 'refreshed'],         'summary' => 'Fully recovered and feeling strong. Sometimes being forced to rest reminds you how good health feels.'],
                'noon'    => ['mood' => 'positive', 'keywords' => ['work', 'catch up', 'productive', 'flow'],       'summary' => 'Powered through a backlog at work and got into a productive flow state. Everything clicked today.'],
                'evening' => null,
            ],
            14 => [
                'morning' => ['mood' => 'neutral',  'keywords' => ['steady', 'coffee', 'tasks', 'morning'],         'summary' => 'A steady, no-surprises morning. Coffee was good and the task list was manageable.'],
                'noon'    => ['mood' => 'positive', 'keywords' => ['mentor', 'guidance', 'inspired', 'talk'],       'summary' => 'Had a great conversation with a mentor over lunch. Left feeling inspired and with a clearer direction.'],
                'evening' => ['mood' => 'positive', 'keywords' => ['stargazing', 'calm', 'wonder', 'night'],        'summary' => 'Spent part of the evening stargazing. The universe has a way of putting things in perspective.'],
            ],
            13 => [
                'morning' => ['mood' => 'negative', 'keywords' => ['argument', 'tense', 'upset', 'hurt'],           'summary' => 'A difficult argument in the morning left Alex feeling tense and hurt.'],
                'noon'    => ['mood' => 'neutral',  'keywords' => ['space', 'reflect', 'walk', 'process'],          'summary' => 'Took a long walk during lunch to process the morning. Space and movement always help.'],
                'evening' => ['mood' => 'positive', 'keywords' => ['resolved', 'apology', 'relief', 'connect'],     'summary' => 'Resolved the conflict with an honest conversation. The relief of reconnection was profound.'],
            ],
            12 => [
                'morning' => ['mood' => 'positive', 'keywords' => ['habit', 'journaling', 'committed', 'routine'],  'summary' => 'Journaling is starting to feel like a natural habit rather than an effort. Progress.'],
                'noon'    => ['mood' => 'positive', 'keywords' => ['lunch', 'solo', 'reflect', 'mindful'],          'summary' => 'Ate lunch alone intentionally, using the time to be present and mindful.'],
                'evening' => ['mood' => 'neutral',  'keywords' => ['wind down', 'podcast', 'notes', 'quiet'],       'summary' => 'A quiet evening with a podcast and some light notes. The routine is becoming comforting.'],
            ],
            11 => [
                'morning' => ['mood' => 'positive', 'keywords' => ['early', 'productive', 'sunrise', 'flow'],       'summary' => 'Up early and riding a wave of productivity. The sunrise made everything feel more intentional.'],
                'noon'    => null,
                'evening' => ['mood' => 'positive', 'keywords' => ['art', 'creative', 'sketch', 'flow'],            'summary' => 'Spent the evening sketching. A creative side that doesn\'t get enough attention finally came out.'],
            ],
            10 => [
                'morning' => ['mood' => 'neutral',  'keywords' => ['planning', 'calendar', 'structure', 'week'],    'summary' => 'Mapped out the week ahead with care. A clear structure reduces the mental load significantly.'],
                'noon'    => ['mood' => 'positive', 'keywords' => ['surprise', 'gift', 'touched', 'kind'],          'summary' => 'Received an unexpected gift from a colleague. A kind gesture that reminded Alex how thoughtful people can be.'],
                'evening' => ['mood' => 'positive', 'keywords' => ['grateful', 'kindness', 'warm', 'reflection'],   'summary' => 'Reflected on the kindness shown today. Gratitude came easily tonight.'],
            ],
            9 => [
                'morning' => ['mood' => 'negative', 'keywords' => ['overwhelmed', 'tasks', 'stress', 'too much'],   'summary' => 'The task list felt impossibly long this morning. Overwhelm crept in before 9 AM.'],
                'noon'    => ['mood' => 'neutral',  'keywords' => ['prioritize', 'breathe', 'one step', 'focus'],   'summary' => 'Paused, reprioritized, and reminded myself to take it one step at a time.'],
                'evening' => ['mood' => 'neutral',  'keywords' => ['done enough', 'rest', 'accept', 'let go'],      'summary' => 'Accepted that enough was done today. Let go of the rest. Tomorrow is another day.'],
            ],
            8 => [
                'morning' => ['mood' => 'positive', 'keywords' => ['better', 'clearer', 'lighter', 'reset'],        'summary' => 'Woke up lighter. Yesterday\'s stress seems to have dissolved overnight. A clean mental slate.'],
                'noon'    => ['mood' => 'positive', 'keywords' => ['team win', 'collaboration', 'result', 'proud'],  'summary' => 'The team pulled off something impressive together. Collaboration at its finest.'],
                'evening' => ['mood' => 'positive', 'keywords' => ['celebration', 'toast', 'team', 'joy'],          'summary' => 'Small celebration with the team. These moments of collective joy make the hard days worth it.'],
            ],
            7 => [
                'morning' => ['mood' => 'neutral',  'keywords' => ['sunday', 'slow', 'lazy', 'coffee'],             'summary' => 'A proper lazy Sunday morning with coffee and no agenda. Alex needed this emptiness.'],
                'noon'    => ['mood' => 'positive', 'keywords' => ['family', 'lunch', 'home', 'warmth'],            'summary' => 'Family lunch at home. The warmth and noise of everyone together was deeply comforting.'],
                'evening' => ['mood' => 'positive', 'keywords' => ['ready', 'new week', 'calm', 'prepared'],        'summary' => 'Felt genuinely ready for the week ahead. Calm, prepared, and at peace.'],
            ],
            6 => [
                'morning' => ['mood' => 'positive', 'keywords' => ['monday', 'fresh', 'goals', 'energy'],           'summary' => 'Mondays don\'t always feel like the enemy. Today Alex stepped in with real energy and clear goals.'],
                'noon'    => ['mood' => 'neutral',  'keywords' => ['meetings', 'notes', 'follow up', 'busy'],       'summary' => 'Back-to-back meetings all morning bled into noon. Lots of notes to follow up on.'],
                'evening' => ['mood' => 'neutral',  'keywords' => ['decompress', 'music', 'bath', 'reset'],         'summary' => 'Decompressed with music and a long bath. A deliberate act of self-care.'],
            ],
            5 => [
                'morning' => ['mood' => 'positive', 'keywords' => ['breakthrough', 'idea', 'excited', 'spark'],     'summary' => 'A breakthrough idea arrived in the shower. Alex scrambled to write it down before it slipped away.'],
                'noon'    => ['mood' => 'positive', 'keywords' => ['develop', 'plan', 'potential', 'vision'],       'summary' => 'Spent lunch developing the idea into something tangible. The potential feels real.'],
                'evening' => ['mood' => 'positive', 'keywords' => ['share', 'feedback', 'validation', 'next steps'],'summary' => 'Shared the idea with a trusted friend who gave honest, encouraging feedback.'],
            ],
            4 => [
                'morning' => ['mood' => 'neutral',  'keywords' => ['implementing', 'work', 'detail', 'grind'],      'summary' => 'The unglamorous work of turning ideas into reality. Details, details, details.'],
                'noon'    => null,
                'evening' => ['mood' => 'positive', 'keywords' => ['progress', 'visible', 'momentum', 'building'],  'summary' => 'By evening the progress was visible. Momentum is building and that feeling is addictive.'],
            ],
            3 => [
                'morning' => ['mood' => 'positive', 'keywords' => ['run', 'cold air', 'alive', 'sharp'],            'summary' => 'A cold morning run that left Alex gasping and grinning. The cold air made everything feel alive.'],
                'noon'    => ['mood' => 'positive', 'keywords' => ['conversation', 'deep', 'connection', 'friend'],  'summary' => 'A deep and honest conversation with an old friend over lunch. These are the connections that matter.'],
                'evening' => ['mood' => 'positive', 'keywords' => ['content', 'full heart', 'grateful', 'today'],   'summary' => 'Alex ended the day with a full heart. Genuinely grateful for the richness of today.'],
            ],
            2 => [
                'morning' => ['mood' => 'neutral',  'keywords' => ['cloudy', 'inside', 'work', 'focus'],            'summary' => 'Cloudy outside, but the indoor focus was sharp. Alex found a rhythm and stayed in it.'],
                'noon'    => ['mood' => 'neutral',  'keywords' => ['admin', 'emails', 'tedious', 'done'],           'summary' => 'Cleared the admin backlog. Not exciting work, but necessary and now it\'s done.'],
                'evening' => ['mood' => 'positive', 'keywords' => ['bath', 'candle', 'book', 'perfect'],            'summary' => 'A bath, a candle, and the last chapters of a great book. A near-perfect evening.'],
            ],
            1 => [
                'morning' => ['mood' => 'positive', 'keywords' => ['excited', 'demo', 'ready', 'prepared'],         'summary' => 'Alex woke up excited. Everything feels aligned and prepared. Today is going to be a good day.'],
                'noon'    => ['mood' => 'positive', 'keywords' => ['confident', 'clear', 'focused', 'sharp'],       'summary' => 'Feeling confident and sharp heading into the afternoon. The work speaks for itself.'],
                'evening' => ['mood' => 'positive', 'keywords' => ['proud', 'journey', 'journvo', 'grateful'],      'summary' => 'Reflecting on the journey. So much has been logged, processed, and grown through. Grateful for every entry.'],
            ],
            0 => [
                'morning' => ['mood' => 'positive', 'keywords' => ['today', 'present', 'alive', 'here'],            'summary' => 'Here, present, and making the most of today. The morning felt like a gift.'],
                'noon'    => ['mood' => 'positive', 'keywords' => ['momentum', 'flow', 'working', 'great'],         'summary' => 'Everything is flowing beautifully. This is what it feels like when work and life align.'],
                'evening' => null,
            ],
        ];

        foreach ($days as $daysAgo => $periods) {
            $date = $today->copy()->subDays($daysAgo);

            $periodSummaries = [];

            foreach ($periods as $period => $data) {
                if ($data === null) continue;

                $hour = match($period) { 'morning' => 8, 'noon' => 12, 'evening' => 19 };

                // Create conversation
                $conversation = Conversation::create([
                    'user_id'     => $user->id,
                    'time_of_day' => $period,
                    'created_at'  => $date->copy()->setHour($hour)->setMinute(0),
                    'updated_at'  => $date->copy()->setHour($hour + 1)->setMinute(0),
                ]);

                // Create messages
                $messages = $this->sampleMessages($period, $data['mood']);
                foreach ($messages as $i => $msg) {
                    Message::create([
                        'conversation_id' => $conversation->id,
                        'sender_type'     => $msg['sender_type'],
                        'content'         => $msg['content'],
                        'created_at'      => $date->copy()->setHour($hour)->setMinute($i * 3),
                        'updated_at'      => $date->copy()->setHour($hour)->setMinute($i * 3),
                    ]);
                }

                // Create period entry
                JournalEntry::create([
                    'user_id'     => $user->id,
                    'entry_date'  => $date->toDateString(),
                    'time_of_day' => $period,
                    'summary'     => $data['summary'],
                    'mood'        => $data['mood'],
                    'keywords'    => $data['keywords'],
                    'created_at'  => $date->copy()->setHour($hour + 1),
                    'updated_at'  => $date->copy()->setHour($hour + 1),
                ]);

                $periodSummaries[] = $data;
            }

            if (empty($periodSummaries)) continue;

            // Overall mood by majority
            $moodCounts = array_count_values(array_column($periodSummaries, 'mood'));
            arsort($moodCounts);
            $overallMood = array_key_first($moodCounts);

            // Merged keywords
            $allKeywords = array_values(array_unique(array_merge(...array_column($periodSummaries, 'keywords'))));
            $allKeywords = array_slice($allKeywords, 0, 8);

            // Overall summary
            $summaries = array_column($periodSummaries, 'summary');
            $overall   = count($summaries) === 1
                ? $summaries[0]
                : (count($summaries) === 2
                    ? $summaries[0] . ' Later, ' . lcfirst($summaries[1])
                    : $summaries[0] . ' As the day continued, ' . lcfirst($summaries[1]) . ' By evening, ' . lcfirst($summaries[2]));

            JournalEntry::create([
                'user_id'     => $user->id,
                'entry_date'  => $date->toDateString(),
                'time_of_day' => 'overall',
                'summary'     => $overall,
                'mood'        => $overallMood,
                'keywords'    => $allKeywords,
                'created_at'  => $date->copy()->setHour(21),
                'updated_at'  => $date->copy()->setHour(21),
            ]);
        }

        $this->command->info('');
        $this->command->info('✦ Demo user seeded successfully!');
        $this->command->info('  Email    : demo@journvo.com');
        $this->command->info('  Password : demo1234');
        $this->command->info('  Notebooks: ' . JournalEntry::where('user_id', $user->id)->where('time_of_day', 'overall')->count());
        $this->command->info('  Streak   : 12+ consecutive days');
        $this->command->info('');
    }

    private function sampleMessages(string $period, string $mood): array
    {
        $sets = [
            'morning' => [
                'positive' => [
                    ['sender_type' => 'user', 'content' => 'Good morning! I actually woke up feeling great today.'],
                    ['sender_type' => 'ai',   'content' => 'That\'s a wonderful way to start the day! What\'s got you feeling so good this morning?'],
                    ['sender_type' => 'user', 'content' => 'I think it\'s the sunshine and I had a really solid sleep. Feeling energized.'],
                    ['sender_type' => 'ai',   'content' => 'Good sleep really does change everything. What are you looking forward to today?'],
                    ['sender_type' => 'user', 'content' => 'I have a big presentation but honestly I feel ready for it.'],
                ],
                'neutral' => [
                    ['sender_type' => 'user', 'content' => 'Morning. Just another regular start to the day.'],
                    ['sender_type' => 'ai',   'content' => 'Good morning! How are you feeling heading into today?'],
                    ['sender_type' => 'user', 'content' => 'Pretty normal, nothing special. Got my coffee, about to start work.'],
                    ['sender_type' => 'ai',   'content' => 'Sometimes a steady quiet morning is exactly what we need. Anything on your mind?'],
                    ['sender_type' => 'user', 'content' => 'Not really, just going to take it one task at a time.'],
                ],
                'negative' => [
                    ['sender_type' => 'user', 'content' => 'Ugh, rough start. Woke up late and already feel behind.'],
                    ['sender_type' => 'ai',   'content' => 'That sounds really stressful. Waking up behind schedule can throw off the whole day. What happened?'],
                    ['sender_type' => 'user', 'content' => 'Alarm didn\'t go off and I have a meeting in 20 minutes.'],
                    ['sender_type' => 'ai',   'content' => 'That\'s a lot of pressure first thing. Is there anything you can do to make the next 20 minutes easier?'],
                    ['sender_type' => 'user', 'content' => 'Just going to grab coffee and jump in. Wish me luck.'],
                ],
            ],
            'noon' => [
                'positive' => [
                    ['sender_type' => 'user', 'content' => 'Just had a really productive morning and the meeting went great!'],
                    ['sender_type' => 'ai',   'content' => 'That sounds fantastic! What made the meeting go so well?'],
                    ['sender_type' => 'user', 'content' => 'The client loved our proposal. We might be getting a new contract.'],
                    ['sender_type' => 'ai',   'content' => 'That\'s exciting news! How are you feeling about it all?'],
                    ['sender_type' => 'user', 'content' => 'Really proud and relieved. Months of work might finally pay off.'],
                ],
                'neutral' => [
                    ['sender_type' => 'user', 'content' => 'Taking a lunch break. Morning was okay, nothing eventful.'],
                    ['sender_type' => 'ai',   'content' => 'Glad you\'re taking a break! What did you get up to this morning?'],
                    ['sender_type' => 'user', 'content' => 'Emails, a couple of calls, the usual. Nothing that stands out.'],
                    ['sender_type' => 'ai',   'content' => 'What are you having for lunch? Sometimes the small things are worth noting.'],
                    ['sender_type' => 'user', 'content' => 'Leftovers from last night. Simple but good.'],
                ],
                'negative' => [
                    ['sender_type' => 'user', 'content' => 'The morning was really hard. Had a conflict with my manager.'],
                    ['sender_type' => 'ai',   'content' => 'That sounds really difficult. Do you want to talk about what happened?'],
                    ['sender_type' => 'user', 'content' => 'We disagreed about the direction of a project. It got a bit heated.'],
                    ['sender_type' => 'ai',   'content' => 'That kind of tension can be really draining. How are you feeling about it now?'],
                    ['sender_type' => 'user', 'content' => 'Still frustrated but trying to eat lunch and reset.'],
                ],
            ],
            'evening' => [
                'positive' => [
                    ['sender_type' => 'user', 'content' => 'What a day! Everything came together beautifully.'],
                    ['sender_type' => 'ai',   'content' => 'That\'s wonderful to hear! What was the highlight of your day?'],
                    ['sender_type' => 'user', 'content' => 'Finished a project I\'ve been working on for weeks. It feels amazing.'],
                    ['sender_type' => 'ai',   'content' => 'That sense of completion is so satisfying. How are you celebrating?'],
                    ['sender_type' => 'user', 'content' => 'Going out for dinner with friends. Can\'t wait.'],
                ],
                'neutral' => [
                    ['sender_type' => 'user', 'content' => 'Day is winding down. It was a pretty standard day overall.'],
                    ['sender_type' => 'ai',   'content' => 'How are you feeling as the day closes out?'],
                    ['sender_type' => 'user', 'content' => 'Tired but okay. Got through what I needed to.'],
                    ['sender_type' => 'ai',   'content' => 'That\'s worth acknowledging. What\'s one thing from today you\'re glad happened?'],
                    ['sender_type' => 'user', 'content' => 'Honestly, just the quiet moments. I needed those today.'],
                ],
                'negative' => [
                    ['sender_type' => 'user', 'content' => 'Really tough day. Feeling drained and a bit low.'],
                    ['sender_type' => 'ai',   'content' => 'I\'m sorry to hear that. What made today so hard?'],
                    ['sender_type' => 'user', 'content' => 'A lot of things piled up. Work, personal stuff. Just too much at once.'],
                    ['sender_type' => 'ai',   'content' => 'That sounds exhausting. What does rest look like for you tonight?'],
                    ['sender_type' => 'user', 'content' => 'Probably just going to sleep early and hope tomorrow is better.'],
                ],
            ],
        ];

        return $sets[$period][$mood] ?? $sets[$period]['neutral'];
    }
}