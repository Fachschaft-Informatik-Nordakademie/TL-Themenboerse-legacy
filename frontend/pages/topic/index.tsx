import {
  AppBar,
  makeStyles,
  Typography,
  Toolbar,
  Container,
  Paper,
  TableContainer,
  TableCell,
  TableBody,
  TableRow,
  TableHead,
  Table,
  Chip,
  Button,
  TablePagination,
  LabelDisplayedRowsArgs,
  TableSortLabel,
} from '@material-ui/core';
import clsx from 'clsx';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { fetchUser } from '../../src/server/fetchUser';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import { User } from '../../src/types/user';
import { useEffect, useState } from 'react';
import axiosClient from '../../src/api';
import Link from 'next/link';
import { format } from 'date-fns';
import { Topic } from '../../src/types/topic';
import { ApiResult } from '../../src/types/api-result';

const drawerWidth = 240;

type Props = {
  user: User;
};

export async function getServerSideProps(context: GetServerSidePropsContext): Promise<GetServerSidePropsResult<Props>> {
  const user = await fetchUser(context.req.cookies);

  if (user === null) {
    return { redirect: { destination: '/login', permanent: false } };
  }

  return {
    props: { user, ...(await serverSideTranslations('de', ['common', 'topic-creation'])) },
  };
}

const useStyles = makeStyles((theme) => ({
  root: {
    display: 'flex',
  },
  toolbar: {
    paddingRight: 24, // keep right padding when drawer closed
  },
  toolbarIcon: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'flex-end',
    padding: '0 8px',
    ...theme.mixins.toolbar,
  },
  appBar: {
    zIndex: theme.zIndex.drawer + 1,
    transition: theme.transitions.create(['width', 'margin'], {
      easing: theme.transitions.easing.sharp,
      duration: theme.transitions.duration.leavingScreen,
    }),
  },
  appBarShift: {
    marginLeft: drawerWidth,
    width: `calc(100% - ${drawerWidth}px)`,
    transition: theme.transitions.create(['width', 'margin'], {
      easing: theme.transitions.easing.sharp,
      duration: theme.transitions.duration.enteringScreen,
    }),
  },
  menuButton: {
    marginRight: 36,
  },
  menuButtonHidden: {
    display: 'none',
  },
  title: {
    flexGrow: 1,
  },
  drawerPaper: {
    position: 'relative',
    whiteSpace: 'nowrap',
    width: drawerWidth,
    transition: theme.transitions.create('width', {
      easing: theme.transitions.easing.sharp,
      duration: theme.transitions.duration.enteringScreen,
    }),
  },
  drawerPaperClose: {
    overflowX: 'hidden',
    transition: theme.transitions.create('width', {
      easing: theme.transitions.easing.sharp,
      duration: theme.transitions.duration.leavingScreen,
    }),
    width: theme.spacing(7),
    [theme.breakpoints.up('sm')]: {
      width: theme.spacing(9),
    },
  },
  appBarSpacer: theme.mixins.toolbar,
  content: {
    flexGrow: 1,
    height: '100vh',
    overflow: 'auto',
  },
  container: {
    paddingTop: theme.spacing(4),
    paddingBottom: theme.spacing(4),
  },
  paper: {
    padding: theme.spacing(2),
    display: 'flex',
    overflow: 'auto',
    flexDirection: 'column',
  },
  fixedHeight: {
    height: 240,
  },
  table: {
    minWidth: 650,
  },
  createButton: {
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
}));

export default function TopicList({ user }: Props): JSX.Element {
  const classes = useStyles();
  const [data, setData] = useState<ApiResult<Topic> | null>(null);
  const [page, setPage] = useState<number>(0);
  const [order, setOrder] = useState<'desc' | 'asc'>('asc');
  const [orderBy, setOrderBy] = useState<string>('deadline');

  const fetchData = async (): Promise<void> => {
    const response = await axiosClient.get<ApiResult<Topic>>(`/topic?page=${page}&order=${order}&orderBy=${orderBy}`);
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
    <div>
      <AppBar position="absolute" className={clsx(classes.appBar)}>
        <Toolbar className={classes.toolbar}>
          <Typography component="h1" variant="h6" color="inherit" noWrap className={classes.title}>
            Themenbörse
          </Typography>
          <div>Eingeloggt als {user.email}</div>
        </Toolbar>
      </AppBar>
      <main className={classes.content}>
        <div className={classes.appBarSpacer} />
        <Container maxWidth="lg" className={classes.container}>
          <Link href="/topic/create">
            <Button className={classes.createButton} variant="contained" color="primary">
              Thema erstellen
            </Button>
          </Link>
          <div className={classes.table}>
            <TableContainer component={Paper}>
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
                      <TableRow key={row.id}>
                        <TableCell component="th" scope="row">
                          {row.title}
                        </TableCell>
                        <TableCell>
                          {row.start ? format(new Date(row.start), 'dd.MM.yyyy') : '(keine Angabe)'}
                        </TableCell>
                        <TableCell>
                          {row.deadline ? format(new Date(row.deadline), 'dd.MM.yyyy') : '(keine Angabe)'}
                        </TableCell>
                        <TableCell>{row.status}</TableCell>
                        <TableCell>
                          {row.tags.map((t) => (
                            <Chip key={t} label={t} />
                          ))}
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
        </Container>
      </main>
    </div>
  );
}
