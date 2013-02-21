-- Copyright (C) 2013 Masood Ahmed

-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.

-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
-- GNU General Public License for more details.

-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <http://www.gnu.org/licenses/>.

--
-- Table structure for table `attendance`
--

INSERT INTO `config`
(
  `path`,
  `value`
)
VALUES
(
  'core/version',
  '1.0.0'
),
(
  'email/host',
  'localhost'
),
(
  'email/username',
  ''
),
(
  'email/password',
  ''
),
(
  'email/port',
  '25'
),
(
  'email/ssl',
  '0'
),
(
  'attendance/days_target_time',
  '8'
),
(
  'attendance/leaves_per_month',
  '2'
),
(
  'attendance/leaves_per_quarter',
  '6'
),
(
  'attendance/leaves_per_year',
  '24'
),
(
  'attendance/leaves_method',
  'quarter'
),
(
  'attendance/leaves_carries',
  '1'
),
(
  'attendance/weekoffs',
  '6,7'
);
