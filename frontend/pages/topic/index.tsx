import {
  Accordion,
  AccordionActions,
  AccordionDetails,
  AccordionSummary,
  Button,
  Checkbox,
  Chip,
  Divider,
  FormControlLabel,
  LabelDisplayedRowsArgs,
  makeStyles,
  Paper,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TablePagination,
  TableRow,
  TableSortLabel,
  TextField,
  Typography,
} from '@material-ui/core';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { fetchUser } from '../../src/server/fetchUser';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import { User } from '../../src/types/user';
import React, { useEffect, useState } from 'react';
import axiosClient from '../../src/api';
import Link from 'next/link';
import { format } from 'date-fns';
import { Topic } from '../../src/types/topic';
import { ApiResult } from '../../src/types/api-result';
import { useRouter } from 'next/router';
import { PageComponent } from '../../src/types/PageComponent';
import SearchBar from 'material-ui-search-bar';
import ExpandMoreIcon from '@material-ui/icons/ExpandMore';
import { Autocomplete } from '@material-ui/lab';
import { KeyboardDatePicker, MuiPickersUtilsProvider } from '@material-ui/pickers';
import DateFnsUtils from '@date-io/date-fns';
import clsx from 'clsx';
import Grid from '@material-ui/core/Grid';

type Props = {
  user: User;
};

export async function getServerSideProps(context: GetServerSidePropsContext): Promise<GetServerSidePropsResult<Props>> {
  const user = await fetchUser(context.req.cookies);

  if (user === null) {
    return { redirect: { destination: '/login', permanent: false } };
  }

  return {
    props: { user, ...(await serverSideTranslations('de', ['common', 'topic'])) },
  };
}

const useStyles = makeStyles((theme) => ({
  table: {
    minWidth: 650,
  },
  controlItem: {
    marginBottom: theme.spacing(2),
  },
  tablePaginationSizeSelectRoot: {
    display: 'none',
  },
  visuallyHidden: {
    border: 0,
    clip: 'rect(0 0 0 0)',
    height: 1,
    margin: -1,
    overflow: 'hidden',
    padding: 0,
    position: 'absolute',
    top: 20,
    width: 1,
  },
  tags: {
    marginLeft: theme.spacing(-1),
  },
  tagItem: { marginLeft: theme.spacing(1) },
  tableHover: {
    cursor: 'pointer',
  },
  searchBar: {
    width: '100%',
    marginBottom: theme.spacing(2),
  },
  column: {},
  onlyOpenCheckbox: {
    margin: theme.spacing(2, 0, 1, -1),
  },
}));

const TopicList: PageComponent<Props> = (): JSX.Element => {
  const classes = useStyles();
  const dateFormat = 'yyyy-MM-dd';
  const [data, setData] = useState<ApiResult<Topic> | null>(null);
  const [page, setPage] = useState<number>(0);
  const [order, setOrder] = useState<'desc' | 'asc'>('asc');
  const [orderBy, setOrderBy] = useState<string>('deadline');
  const [text, setText] = useState<string>('');
  const [tags, setTags] = useState<string[]>([]);
  const [onlyOpen, setOnlyOpen] = useState<boolean>(true);
  const [startFrom, setStartFrom] = useState<Date>(null);
  const [startUntil, setStartUntil] = useState<Date>(null);
  const [endFrom, setEndFrom] = useState<Date>(null);
  const [endUntil, setEndUntil] = useState<Date>(null);

  const router = useRouter();

  const fetchData = async (): Promise<void> => {
    const response = await axiosClient.get<ApiResult<Topic>>(
      `/topic?page=${page}&order=${order}&orderBy=${orderBy}&text=${text}&tags=${tags}&onlyOpen=${onlyOpen}&startFrom=${
        startFrom ? format(startFrom, dateFormat) : ''
      }&startUntil=${startUntil ? format(startUntil, dateFormat) : ''}&endFrom=${
        endFrom ? format(endFrom, dateFormat) : ''
      }&endUntil=${endUntil ? format(endUntil, dateFormat) : ''}`,
    );
    setData(response.data);
  };

  const handleRequestSort = (property) => (): void => {
    const isAsc = orderBy === property && order === 'asc';
    setOrder(isAsc ? 'desc' : 'asc');
    setOrderBy(property);
    setPage(0);
  };

  useEffect(() => {
    fetchData();
  }, [page, order, orderBy]);

  return (
    <>
      <Typography gutterBottom variant="h4" component="h2">
        Themenübersicht
      </Typography>
      <Link href="/topic/create">
        <Button className={classes.controlItem} variant="contained" color="primary">
          Thema erstellen
        </Button>
      </Link>

      <SearchBar
        className={classes.searchBar}
        value={text}
        onChange={(newValue) => setText(newValue)}
        onRequestSearch={() => fetchData()}
        placeholder={'Suchen'}
        style={{ borderRadius: 0 }}
      />

      <Accordion className={clsx(classes.controlItem, 'filter-accordion')}>
        <AccordionSummary expandIcon={<ExpandMoreIcon />} aria-label="Expand">
          Erweiterte Filter
        </AccordionSummary>
        <AccordionDetails>
          <Grid container>
            <Grid item xs={12}>
              <Autocomplete
                className={classes.column}
                id="tags"
                multiple
                freeSolo
                fullWidth
                value={tags}
                onChange={(e, values) => setTags(values)}
                options={tagOptions}
                getOptionLabel={(option) => option}
                defaultValue={[]}
                renderInput={(params) => <TextField {...params} variant="standard" label={'Tags'} />}
              />
            </Grid>
            <Grid item xs={12}>
              <FormControlLabel
                className={classes.onlyOpenCheckbox}
                control={
                  <Checkbox checked={onlyOpen} onChange={(e) => setOnlyOpen(e.target.checked)} color="primary" />
                }
                label="Nur offene Themen anzeigen"
              />
            </Grid>
            <MuiPickersUtilsProvider utils={DateFnsUtils}>
              <Grid item xs={3}>
                <KeyboardDatePicker
                  className={classes.column}
                  disableToolbar
                  variant="inline"
                  format={dateFormat}
                  margin="normal"
                  label="Start von:"
                  value={startFrom}
                  onChange={setStartFrom}
                />
              </Grid>
              <Grid item xs={3}>
                <KeyboardDatePicker
                  className={classes.column}
                  disableToolbar
                  variant="inline"
                  format={dateFormat}
                  margin="normal"
                  label="Start bis:"
                  value={startUntil}
                  onChange={setStartUntil}
                />
              </Grid>
              <Grid item xs={3}>
                <KeyboardDatePicker
                  className={classes.column}
                  disableToolbar
                  variant="inline"
                  format={dateFormat}
                  margin="normal"
                  label="Ende von:"
                  value={endFrom}
                  onChange={setEndFrom}
                />
              </Grid>
              <Grid item xs={3}>
                <KeyboardDatePicker
                  className={classes.column}
                  disableToolbar
                  variant="inline"
                  format={dateFormat}
                  margin="normal"
                  label="Ende bis:"
                  value={endUntil}
                  onChange={setEndUntil}
                />
              </Grid>
            </MuiPickersUtilsProvider>
          </Grid>
        </AccordionDetails>
        <Divider />
        <AccordionActions>
          <Button size="small" color="primary" onClick={() => fetchData()}>
            Suchen
          </Button>
        </AccordionActions>
      </Accordion>
      <div className={classes.table}>
        <TableContainer component={Paper} style={{ borderRadius: 0 }}>
          <Table className={classes.table} aria-label="simple table">
            <TableHead>
              <TableRow>
                <TableCell>Titel</TableCell>
                <TableCell>
                  <TableSortLabel
                    active={orderBy === 'start'}
                    direction={orderBy === 'start' ? order : 'asc'}
                    onClick={handleRequestSort('start')}
                  >
                    Startdatum
                    {orderBy === 'start' ? (
                      <span className={classes.visuallyHidden}>
                        {order === 'desc' ? 'sorted descending' : 'sorted ascending'}
                      </span>
                    ) : null}
                  </TableSortLabel>
                </TableCell>
                <TableCell>
                  <TableSortLabel
                    active={orderBy === 'deadline'}
                    direction={orderBy === 'deadline' ? order : 'asc'}
                    onClick={handleRequestSort('deadline')}
                  >
                    Enddatum
                    {orderBy === 'deadline' ? (
                      <span className={classes.visuallyHidden}>
                        {order === 'desc' ? 'sorted descending' : 'sorted ascending'}
                      </span>
                    ) : null}
                  </TableSortLabel>
                </TableCell>
                <TableCell>Status</TableCell>
                <TableCell>Tags</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {data &&
                data.content.map((row) => (
                  <TableRow
                    key={row.id}
                    onClick={() => router.push('/topic/' + row.id)}
                    hover
                    classes={{ hover: classes.tableHover }}
                  >
                    <TableCell component="th" scope="row">
                      {row.title}
                    </TableCell>
                    <TableCell>{row.start ? format(new Date(row.start), 'dd.MM.yyyy') : '(keine Angabe)'}</TableCell>
                    <TableCell>
                      {row.deadline ? format(new Date(row.deadline), 'dd.MM.yyyy') : '(keine Angabe)'}
                    </TableCell>
                    <TableCell>{row.status}</TableCell>
                    <TableCell>
                      <span className={classes.tags}>
                        {row.tags.map((t) => (
                          <Chip classes={{ root: classes.tagItem }} key={t} label={t} />
                        ))}
                      </span>
                    </TableCell>
                  </TableRow>
                ))}
            </TableBody>
          </Table>
        </TableContainer>
        {data && (
          <TablePagination
            component="div"
            count={data.total}
            rowsPerPage={data.perPage}
            labelRowsPerPage=""
            page={page}
            rowsPerPageOptions={[{ value: data.perPage, label: data.perPage.toString() }]}
            labelDisplayedRows={(paginationInfo: LabelDisplayedRowsArgs) =>
              `Zeige Elemente ${paginationInfo.from}-${paginationInfo.to} (von insgesamt ${paginationInfo.count})`
            }
            classes={{ selectRoot: classes.tablePaginationSizeSelectRoot }}
            onChangePage={(e, newPage) => {
              setPage(newPage);
            }}
          />
        )}
      </div>
    </>
  );
};

const tagOptions: string[] = []; // TODO: hard coded defaults or values loaded from the backend?

TopicList.layout = 'main';
export default TopicList;
