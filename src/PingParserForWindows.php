<?php

namespace Acamposm\Ping;

use stdClass;

class PingParserForWindows extends PingParser
{
    /**
     * PingParserForWindows constructor.
     * @param array $ping
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(array $ping)
    {
        if (empty($ping) === false) {
            $this->round_trip_time = $this->GetRoundTripTimeStatistics($ping);
            $this->raw = $ping;
            $this->sequence = $this->GetSequence($ping);
            $this->statistics = $this->GetPingStatistics($ping);
            //
            $this->latency = $this->GetLatency();
            $this->result = $this->GetResult();
        }

        return $this;
    }

    /**
     * Return an array with the PING statistics.
     *
     * @param  array  $ping
     * @return  stdClass
     */
    private function GetPingStatistics(array $ping): stdClass
    {
        $lines = count($ping);

        $ping_statistics = explode(', ', explode(':', $ping[$lines - 4])[1]);

        $transmitted = (int) explode(' = ', $ping_statistics[0])[1];

        $received = (int) explode(' = ', $ping_statistics[1])[1];

        $lost = (int) explode(' = ', $ping_statistics[2])[1];

        return (object) [
            'packets_transmitted' => $transmitted,
            'packets_received' => $received,
            'packets_lost' => $lost,
            'packet_loss' => (int) (100 - (($received * 100) / $transmitted)),
        ];
    }

    /**
     * Returns an array with Round Trip Time Statistics.
     *
     * @param  array  $ping
     * @return  stdClass
     */
    private function GetRoundTripTimeStatistics(array $ping): stdClass
    {
        $lines = count($ping);

        $rtt = explode(',', str_replace('ms', '', $ping[$lines - 1]));

        $min = (float) explode(' = ', $rtt[0])[1] / 1000;
        $max = (float) explode(' = ', $rtt[1])[1] / 1000;
        $avg = (float) explode(' = ', $rtt[2])[1] / 1000;

        return (object) [
            'avg' => $avg,
            'min' => $min,
            'max' => $max,
        ];
    }

    /**
     * Returns an array with de packet sequence and his latency.
     *
     * @param array $ping
     * @return  array
     */
    private function GetSequence(array $ping): array
    {
        $items_count = count($ping);

        // First remove items from final of the array
        unset($ping[$items_count - 6]);
        unset($ping[$items_count - 5]);
        unset($ping[$items_count - 4]);
        unset($ping[$items_count - 3]);
        unset($ping[$items_count - 2]);
        unset($ping[$items_count - 1]);

        // Then remove first items
        unset($ping[1]);
        unset($ping[0]);

        $key = 0;

        $sequence = [];

        foreach ($ping as $row) {
            $sequence[$key] = $row;

            $key++;
        }

        return $sequence;
    }
}
